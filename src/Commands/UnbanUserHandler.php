<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Commands;

use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use FoF\BanIPs\Events\IPWasUnbanned;
use FoF\BanIPs\Repositories\BannedIPRepository;
use FoF\BanIPs\Validators\BannedIPValidator;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Events\Dispatcher as DispatcherEvents;

class UnbanUserHandler
{
    use AssertPermissionTrait;

    /**
     * @var Dispatcher
     */
    private $bus;

    /**
     * @var DispatcherEvents
     */
    private $events;

    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    /**
     * @var BannedIPValidator
     */
    private $validator;

    /**
     * @param Dispatcher         $bus
     * @param DispatcherEvents   $events
     * @param BannedIPRepository $bannedIPs
     * @param BannedIPValidator  $validator
     */
    public function __construct(Dispatcher $bus, DispatcherEvents $events, BannedIPRepository $bannedIPs, BannedIPValidator $validator)
    {
        $this->bus = $bus;
        $this->events = $events;
        $this->bannedIPs = $bannedIPs;
        $this->validator = $validator;
    }

    /**
     * @param BanUser $command
     *
     * @return mixed
     */
    public function handle(UnbanUser $command)
    {
        $actor = $command->actor;

        $user = User::where('id', $command->userId)->orWhere('username', $command->userId)->firstOrFail();

        $this->assertCan($actor, 'banIP', $user);

        $bannedIPs = $this->bannedIPs->getUserBannedIPs($user)->get();

        foreach ($bannedIPs as $bannedIP) {
            $bannedIP->delete();

            $this->events->fire(
                new IPWasUnbanned($bannedIP, $actor)
            );
        }

        return $bannedIPs;
    }
}
