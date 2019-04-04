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

use Flarum\User\User;

class DeleteBannedIP
{
    /**
     * @var int
     */
    public $bannedId;
    /**
     * @var User
     */
    public $actor;

    /**
     * @param int $bannedId
     * @param User $actor
     */
    public function __construct($bannedId, User $actor)
    {
        $this->bannedId = $bannedId;
        $this->actor = $actor;
    }
}
