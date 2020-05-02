<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Events;

use Flarum\User\User;
use FoF\BanIPs\BannedIP;

class IPWasUnbanned
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var BannedIP
     */
    public $unbannedIP;

    /**
     * @param BannedIP $unbannedIP
     * @param User     $actor
     */
    public function __construct(BannedIP $unbannedIP, User $actor)
    {
        $this->unbannedIP = $unbannedIP;
        $this->actor = $actor;
    }
}
