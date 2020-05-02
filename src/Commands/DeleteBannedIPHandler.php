<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

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
