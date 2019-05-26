<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Events;

use Flarum\User\User;
use FoF\BanIPs\BannedIP;

class IPWasBanned
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var BannedIP
     */
    public $bannedIP;

    /**
     * PollWasCreated constructor.
     *
     * @param BannedIP $bannedIP
     * @param User     $actor
     */
    public function __construct(BannedIP $bannedIP, User $actor)
    {
        $this->bannedIP = $bannedIP;
        $this->actor = $actor;
    }
}
