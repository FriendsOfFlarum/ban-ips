<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Commands;

use Flarum\User\User;
use FoF\BanIPs\Events\IPWasUnbanned;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Events\Dispatcher as DispatcherEvents;

class DeleteBannedIPHandler
{
    public function __construct(private DispatcherEvents $events, private BannedIPRepository $bannedIPs)
    {
    }

    public function handle(DeleteBannedIP $command): void
    {
        /**
         * @var User
         */
        $actor = $command->actor;

        $actor->assertPermission($actor->hasPermission('fof.ban-ips.banIP'));

        $banIP = $this->bannedIPs->findOrFail($command->bannedId);

        $banIP->delete();

        $this->events->dispatch(
            new IPWasUnbanned($banIP, $actor)
        );
    }
}
