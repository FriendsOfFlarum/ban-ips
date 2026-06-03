<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Events;

use Flarum\User\User;
use FoF\BanIPs\BannedIP;

class IPWasUnbanned
{
    public function __construct(public BannedIP $unbannedIP, public User $actor)
    {
    }
}
