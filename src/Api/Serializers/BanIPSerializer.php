<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Api\Serializers;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\Api\Serializer\PostSerializer;
use Tobscure\JsonApi\Relationship;

class BanIPSerializer extends AbstractSerializer
{
    /**
     * @var string
     */
    protected $type = 'banned_ip_addresses';

    /**
     * @param array|object $banIP
     * @return array
     */
    protected function getDefaultAttributes($banIP)
    {
        return [
            'userId' => $banIP->user_id,
            'postId' => $banIP->post_id,
            'ipAddress' => $banIP->ip_address,
            'createdAt' => $this->formatDate($banIP->created_at),
        ];
    }

    /**
     * @param $banIP
     * @return Relationship
     */
    protected function post($banIP) {
        return $this->hasOne($banIP, PostSerializer::class);
    }

    /**
     * @param $banIP
     * @return Relationship
     */
    protected function user($banIP) {
        return $this->hasOne($banIP, BasicUserSerializer::class);
    }
}
