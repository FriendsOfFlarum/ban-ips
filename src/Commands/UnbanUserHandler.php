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

class UnbanUserHandler
{
    public function __construct(private DispatcherEvents $events, private BannedIPRepository $bannedIPs)
    {
    }

    /**
     * @param UnbanUser $command
     *
     * @return mixed
     */
    public function handle(UnbanUser $command)
    {
        /**
         * @var User
         */
        $actor = $command->actor;

        $user = User::where('id', $command->userId)->orWhere('username', $command->userId)->firstOrFail();

        $actor->assertCan('banIP', $user);

        $bannedIPs = $this->bannedIPs->getUserBannedIPs($user)->get();

        foreach ($bannedIPs as $bannedIP) {
            /** @var \FoF\BanIPs\BannedIP $bannedIP */
            $bannedIP->delete();

            $this->events->dispatch(
                new IPWasUnbanned($bannedIP, $actor)
            );
        }

        return $bannedIPs;
    }
}
