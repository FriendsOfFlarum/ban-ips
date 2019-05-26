<?php


namespace FoF\BanIPs\Commands;

use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Events\IPWasBanned;
use FoF\BanIPs\Validators\BannedIPValidator;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Events\Dispatcher as DispatcherEvents;
use Illuminate\Support\Arr;

class CreateBannedIPsHandler
{
    use AssertPermissionTrait;

    /**
     * @var Dispatcher
     */
    private $bus;

    /**
     * @var DispatcherEvents
     */
    private $events;

    /**
     * @var BannedIPValidator
     */
    protected $validator;

    /**
     * CreateBannedIPHandler constructor.
     * @param Dispatcher $bus
     * @param DispatcherEvents $events
     * @param BannedIPValidator $validator
     */
    public function __construct(Dispatcher $bus, DispatcherEvents $events, BannedIPValidator $validator)
    {
        $this->bus = $bus;
        $this->events = $events;
        $this->validator = $validator;
    }

    /**
     * @param CreateBannedIPs $command
     * @return mixed
     */
    public function handle(CreateBannedIPs $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        $userId = Arr::get($data, 'attributes.userId');
        $user = User::where('id', $userId)->orWhere('username', $userId)->firstOrFail();
        $reason = Arr::get($data, 'attributes.reason');

        $this->assertCan($actor, 'banIP', $user);

        $ips = $user->posts()->whereNotNull('ip_address')->pluck('ip_address')->unique();

        $bannedIPs = [];

        foreach ($ips as $address) {
            $bannedIP = BannedIP::build(
                $actor->id,
                $user->id,
                $address,
                $reason
            );

            $this->validator->assertValid($bannedIP->getAttributes());

            $bannedIP->save();

            $this->events->fire(
                new IPWasBanned($bannedIP, $actor)
            );

            $bannedIPs[] = $bannedIP;
        }

        return $bannedIPs;
    }
}
