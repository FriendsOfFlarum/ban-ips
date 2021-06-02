<?php

namespace FoF\BanIPs;

use Flarum\Group\Group;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;

class RevokeAccess
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var BannedIPRepository
     */
    protected $bannedIPs;

    public function __construct(SettingsRepositoryInterface $settings, BannedIPRepository $bannedIPs)
    {
        $this->settings = $settings;
        $this->bannedIPs = $bannedIPs;
    }
    
    /**
     * @param User $user
     * @param array $groupIds
     */
    public function __invoke(User $actor, array $groupIds)
    {
        if ($this->bannedIPs->findByIPAddress($actor->accessing_ip)) {
            return [Group::GUEST_ID];
        }

        return $groupIds;
    }
}
