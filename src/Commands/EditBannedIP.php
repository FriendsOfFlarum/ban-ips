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

class EditBannedIP
{
    /**
     * @var int
     */
    public $banId;
    /**
     * @var User
     */
    public $actor;
    /**
     * @var array
     */
    public $data;

    /**
     * @param int $banId
     * @param User $actor
     * @param array $data
     */
    public function __construct($banId, User $actor, array $data)
    {
        $this->banId = $banId;
        $this->actor = $actor;
        $this->data = $data;
    }
}
