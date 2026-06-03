<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Listeners;

use Flarum\User\User;
use FoF\BanIPs\Events\IPWasBanned;
use FoF\BanIPs\Repositories\BannedIPRepository;

class RemoveAccessToBannedUsers
{
    public function __construct(private BannedIPRepository $bannedIPs)
    {
    }

    public function handle(IPWasBanned $event): void
    {
        $bannedIP = $event->bannedIP;
        $users = $this->bannedIPs->findUsers($bannedIP->address);

        foreach ($users as $user) {
            /** @var User $user */
            $user->accessTokens()->delete();
        }
    }
}
