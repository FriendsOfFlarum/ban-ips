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
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\BanIPs\Repositories\BannedIPRepository;

class ForumAttributes
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var BannedIPRepository
     */
    protected $bannedIPs;

    public function __construct(SettingsRepositoryInterface $settings, BannedIPRepository $bannedIPs)
    {
        $this->settings = $settings;
        $this->bannedIPs = $bannedIPs;
    }

    public function __invoke(ForumSerializer $serializer, $model, array $attributes): array
    {
        $actor = $serializer->getActor();

        if ($this->settings->get('fof-ban-ips.show-banned-ip-warning', false) && $restricted = (bool) $this->bannedIPs->findByIPAddress($actor->accessing_ip)) {
            $attributes['ipRestricted'] = $restricted;
        }

        return $attributes;
    }
}
