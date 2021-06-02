<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs;

use Flarum\Api\Serializer\ForumSerializer;
use FoF\BanIPs\Repositories\BannedIPRepository;

class ForumAttributes
{
    /**
     * @var BannedIPRepository
     */
    protected $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    public function __invoke(ForumSerializer $serializer, $model, array $attributes): array
    {
        $actor = $serializer->getActor();

        if ($restricted = (bool) $this->bannedIPs->findByIPAddress($actor->accessing_ip)) {
            $attributes['ipRestricted'] = $restricted;
        }

        return $attributes;
    }
}
