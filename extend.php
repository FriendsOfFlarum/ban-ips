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
use Illuminate\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),
    new Extend\Locales(__DIR__.'/resources/locale'),
    (new Extend\Routes('api'))
        ->get('/fof/ban-ips/check-users', 'fof.ban-ips.check', Api\Controllers\CheckIPsController::class)
        ->get('/fof/ban-ips/check-users/{user}', 'fof.ban-ips.check-other-users', Api\Controllers\CheckIPsController::class)
        ->post('/fof/ban-ips/bans', 'fof.ban-ips.store', Api\Controllers\CreateBannedIPController::class)
        ->post('/fof/ban-ips/bans/chunk', 'fof.ban-ips.store.chunk', Api\Controllers\CreateBannedIPsController::class),
    function (Dispatcher $events) {
        $events->subscribe(Access\UserPolicy::class);

        $events->subscribe(Listeners\AddApiAttributes::class);
        $events->subscribe(Listeners\AddMiddleware::class);
        $events->subscribe(Listeners\RemoveAccessToBannedUsers::class);
    },
];
