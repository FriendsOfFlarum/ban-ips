<?php


namespace FoF\BanIPs\Commands;


use Flarum\User\AssertPermissionTrait;
use FoF\BanIPs\Repositories\BannedIPRepository;

class DeleteBannedIPHandler
{
    use AssertPermissionTrait;

    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    /**
     * @param DeleteBannedIP $command
     *
     * @return BanIP
     */
    public function handle(DeleteBannedIP $command)
    {
        $actor = $command->actor;

        $this->assertCan($actor, 'banIP');

        $banIP = $this->bannedIPs->findOrFail($command->bannedId);

        $banIP->delete();
    }
}