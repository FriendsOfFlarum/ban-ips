<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Listeners;

use Flarum\Event\ConfigureMiddleware;
use FoF\BanIPs\Middleware\RegisterMiddleware;
use Illuminate\Events\Dispatcher;

class AddMiddleware
{
    /**
     * Subscribes to the Flarum events.
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureMiddleware::class, [$this, 'addMiddleware']);
    }

    /**
     * @param ConfigureMiddleware $event
     */
    public function addMiddleware(ConfigureMiddleware $event)
    {
        if (!$event->isAdmin() && !$event->isApi()) {
            $event->pipe(app(RegisterMiddleware::class));
        }
    }
}
