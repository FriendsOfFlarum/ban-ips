<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Api\Serializer;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\Api\Serializer\PostSerializer;

class BanIPSerializer extends AbstractSerializer
{
    /**
     * @var string
     */
    protected $type = 'banned_ip_addresses';

    /**
     * @param array|object $bannedIPAddress
     * @return array
     */
    protected function getDefaultAttributes($bannedIPAddress)
    {
        return [
            'userID' => $bannedIPAddress->user_id,
            'postID' => $bannedIPAddress->post_id,
            'ipAddress' => $bannedIPAddress->ip_address,
            'createdAt' => $this->formatDate($bannedIPAddress->created_at),
        ];
    }

    /**
     * @param $bannedIPAddress
     * @return \Tobscure\JsonApi\Relationship
     */
    protected function post($bannedIPAddress) {
        return $this->hasOne($bannedIPAddress, PostSerializer::class);
    }

    /**
     * @param $bannedIPAddress
     * @return \Tobscure\JsonApi\Relationship
     */
    protected function user($bannedIPAddress) {
        return $this->hasOne($bannedIPAddress, BasicUserSerializer::class);
    }
}
