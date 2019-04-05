<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs;

use Flarum\Foundation\AbstractValidator;

class BanIPValidator extends AbstractValidator
{
    protected $rules = [
        'userId' => ['required', 'integer'],
        'postId' => ['required', 'integer'],
        'ipAddress' => ['required', 'ip'],
    ];
}
