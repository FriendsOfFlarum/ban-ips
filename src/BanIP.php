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
     * @param $userID
     * @param $postID
     * @param $ipAddress
     * @return BanIP
     */
    public static function build($userID, $postID, $ipAddress)
    {
        $banIP = new static();

        $banIP->user_id = $userID;
        $banIP->post_id = $postID;
        $banIP->ip_address = $ipAddress;

        return $banIP;
    }


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
