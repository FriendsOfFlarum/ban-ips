<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Validators;

use Flarum\Foundation\AbstractValidator;

class BannedIPValidator extends AbstractValidator
{
    protected $rules = [
        'userId'  => ['required', 'integer'],
        'address' => ['required', 'ip', 'unique:banned_ips,address'],
        'reason'  => ['nullable', 'string'],
    ];
}
