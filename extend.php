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
use FoF\BanIPs\Api\Controller\ListBannedIPsController;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),
    new Extend\Locales(__DIR__ . '/resources/locale'),
    (new Extend\Routes('api'))
        ->get('/bannedips', 'bannedips.index', ListBannedIPsController::class),
    function (Dispatcher $events) {
    	$events->subscribe(Listeners\AddMiddleware::class);
        $events->subscribe(Listeners\InjectSettings::class);
        $events->subscribe(Listeners\AddPermissions::class);
        $events->subscribe(Access\UserPolicy::class);
    }

];
