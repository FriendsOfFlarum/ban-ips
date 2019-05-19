<?php


namespace FoF\BanIPs\Commands;

use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Validators\BannedIPValidator;
use Illuminate\Support\Arr;

class CreateBannedIPHandler
{
    use AssertPermissionTrait;

    /**
     * @var BannedIPValidator
     */
    protected $validator;

    /**
     * CreateBannedIPHandler constructor.
     * @param BannedIPValidator $validator
     */
    public function __construct(BannedIPValidator $validator)
    {
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
        $user = User::where('id', $userId)->orWhere('username', $userId)->firstOrFail();

        $this->assertCan($actor, 'banIP', $user);

        $bannedIP = BannedIP::build(
            $actor->id,
            $user->id,
            Arr::get($data, 'attributes.address'),
            Arr::get($data, 'attributes.reason')
        );

        $this->validator->assertValid($bannedIP->getAttributes());

        $bannedIP->save();

        return $bannedIP;
    }
}
