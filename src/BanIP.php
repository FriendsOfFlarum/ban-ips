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

use Flarum\Database\AbstractModel;
use Flarum\Post\Post;
use Flarum\User\User;

class BanIP extends AbstractModel
{
    /**
     * @var string
     */
    protected $table = 'banned_ip_addresses';
    /**
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * @var array
     */
    protected $guarded = [];
    /**
     *
     */
    public function post()
    {
        $this->belongsTo(Post::class);
    }
    /**
     *
     */
    public function user()
    {
        $this->belongsTo(User::class);
    }
}
