<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Commands;

use Flarum\User\AssertPermissionTrait;
use Flarum\User\Exception\PermissionDeniedException;
use FoF\BanIPs\BanIP;

class DeleteBannedIPHandler
{
    use AssertPermissionTrait;

    /**
     * @param DeleteBannedIP $command
     *
     * @throws PermissionDeniedException
     *
     * @return BanIP
     */
    public function handle(DeleteBannedIP $command)
    {
        $actor = $command->actor;

        $this->assertCan($actor, 'fof.banips.banIP');

        $banIP = BanIP::where('id', $command->bannedId)->firstOrFail();

        $banIP->delete();

        return $banIP;
    }
}
