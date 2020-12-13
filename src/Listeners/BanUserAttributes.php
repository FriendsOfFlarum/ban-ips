<?php

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
        $attributes['canBanIP'] = $serializer->getActor()->can('banIP', $user);

        return $attributes;
    }
}
