<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs;

use Flarum\Group\Group;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;

class RevokeAccessWhenIPBanned
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
     * @param User  $user
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
