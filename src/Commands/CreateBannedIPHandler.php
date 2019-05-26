<?php


namespace FoF\BanIPs\Commands;

use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Events\IPWasBanned;
use FoF\BanIPs\Validators\BannedIPValidator;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;

class CreateBannedIPHandler
{
    use AssertPermissionTrait;

    /**
     * @var BannedIPValidator
     */
    protected $validator;

    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * CreateBannedIPHandler constructor.
     * @param Dispatcher $events
     * @param BannedIPValidator $validator
     */
    public function __construct(Dispatcher $events, BannedIPValidator $validator)
    {
        $this->events = $events;
        $this->validator = $validator;
    }

    /**
     * @param CreateBannedIP $command
     * @return mixed
     */
    public function handle(CreateBannedIP $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        $userId = Arr::get($data, 'attributes.userId');
        $user = $userId != null ? User::where('id', $userId)->orWhere('username', $userId)->firstOrFail() : null;

        $this->assertCan($actor, 'banIP', $user);

        $bannedIP = BannedIP::build(
            $actor->id,
            $user ? $user->id : null,
            Arr::get($data, 'attributes.address'),
            Arr::get($data, 'attributes.reason')
        );

        $this->validator->assertValid($bannedIP->getAttributes());

        $bannedIP->save();

        $this->events->fire(
            new IPWasBanned($bannedIP, $actor)
        );

        return $bannedIP;
    }
}
