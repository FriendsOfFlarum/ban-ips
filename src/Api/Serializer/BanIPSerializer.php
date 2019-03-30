<?php

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
            'ipAddress' => $bannedIPAddress->ip_address,
            'createdAt' => $this->formatDate($bannedIPAddress->created_at),
        ];
    }

    protected function post($bannedIPAddress) {
        return $this->hasOne($bannedIPAddress, PostSerializer::class);
    }

    protected function user($bannedIPAddress) {
        return $this->hasOne($bannedIPAddress, BasicUserSerializer::class);
    }
}
