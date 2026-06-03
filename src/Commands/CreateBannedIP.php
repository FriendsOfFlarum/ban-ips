<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Commands;

use Flarum\User\User;

class CreateBannedIP
{
    public function __construct(public User $actor, public array $data)
    {
    }
}
