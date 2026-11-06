<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;

class BanUserAttributes
{
    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    public function __invoke(UserSerializer $serializer, User $user, array $attributes): array
    {
        $attributes['isBanned'] = $this->bannedIPs->isUserBanned($user);
        $actor = $serializer->getActor();
        // Only someone who may ban IPs needs the per-user check, which works out that user's permissions.
        $attributes['canBanIP'] = $actor->hasPermission('fof.ban-ips.banIP') && $actor->can('banIP', $user);

        return $attributes;
    }
}
