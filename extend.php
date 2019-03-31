<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs;

use Flarum\Extend;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),
    new Extend\Locales(__DIR__ . '/resources/locale'),
    (new Extend\Routes('api'))
        ->get('/bannedips', 'fof.bannedips.index', Api\Controller\ListBannedIPsController::class),
    function (Dispatcher $events) {
        $events->subscribe(Listener\AddApiAttributes::class);
        $events->subscribe(Listener\AddMiddleware::class);
        $events->subscribe(Access\UserPolicy::class);
    }

];
