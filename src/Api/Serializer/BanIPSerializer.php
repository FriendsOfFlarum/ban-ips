<?php

namespace FoF\BanIPs\Api\Serializer;

use Flarum\Api\Serializer\AbstractSerializer;

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
}
