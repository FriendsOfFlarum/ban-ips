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

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\Post\Post;
use Flarum\User\User;

/**
 * @property integer userId
 * @property integer postId
 * @property string ipAddress
 * @property Carbon time
 */

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
     * @param $userId
     * @param $postId
     * @param $ipAddress
     * @return BanIP
     */
    public static function build($userId, $postId, $ipAddress)
    {
        $banIP = new static();

        $banIP->user_id = $userId;
        $banIP->post_id = $postId;
        $banIP->ip_address = $ipAddress;
        $banIP->created_at = time();

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
