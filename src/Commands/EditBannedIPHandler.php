<?php


namespace FoF\BanIPs\Commands;


use Flarum\User\AssertPermissionTrait;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Repositories\BannedIPRepository;
use FoF\BanIPs\Validators\BannedIPValidator;

class EditBannedIPHandler
{
    use AssertPermissionTrait;

    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    /**
     * @var BannedIPValidator
     */
    private $validator;

    /**
     * @param BannedIPRepository $bannedIPs
     * @param BannedIPValidator $validator
     */
    public function __construct(BannedIPRepository $bannedIPs, BannedIPValidator $validator)
    {
        $this->bannedIPs = $bannedIPs;
        $this->validator = $validator;
    }

    /**
     * @param EditBannedIP $command
     * @return BannedIP
     */
    public function handle(EditBannedIP $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        $attributes = array_get($data, 'attributes', []);

        $this->assertCan($actor, 'banIP');

        $bannedIP = $this->bannedIPs->findOrFail($command->bannedId);

        if (isset($attributes['userId'])) {
            $bannedIP->user_id = $attributes['userId'];
        }

        if (isset($attributes['ipAddress'])) {
            $bannedIP->address = $attributes['ipAddress'];
        }

        if (isset($attributes['reason'])) {
            $bannedIP->address = $attributes['reason'];
        }

        $this->validator->assertValid(array_merge($bannedIP->getDirty()));

        $bannedIP->save();

        return $bannedIP;
    }
}