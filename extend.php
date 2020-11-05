<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs;

use Flarum\Extend;
use Flarum\Post\Post;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Middleware\RegisterMiddleware;
use Illuminate\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),
    new Extend\Locales(__DIR__.'/resources/locale'),
    (new Extend\Routes('api'))
        ->post('/users/{id}/ban', 'fof.ban-ips.users.ban', Api\Controllers\BanUserController::class)
        ->post('/users/{id}/unban', 'fof.ban-ips.users.unban', Api\Controllers\UnbanUserController::class)
        ->get('/users/{id}/banned-ips', 'fof.ban-ips.users.banned-ips', Api\Controllers\ListUserBannedIPsController::class)
        ->get('/fof/ban-ips/check-users', 'fof.ban-ips.check', Api\Controllers\CheckIPsController::class)
        ->get('/fof/ban-ips/check-users/{user}', 'fof.ban-ips.check.others', Api\Controllers\CheckIPsController::class)
        ->get('/fof/ban-ips', 'fof.ban-ips.index', Api\Controllers\ListBannedIPsController::class)
        ->post('/fof/ban-ips', 'fof.ban-ips.store', Api\Controllers\CreateBannedIPController::class)
        ->patch('/fof/ban-ips/{id}', 'fof.ban-ips.update', Api\Controllers\UpdateBannedIPController::class)
        ->delete('/fof/ban-ips/{id}', 'fof.ban-ips.delete', Api\Controllers\DeleteBannedIPController::class),
    (new Extend\Middleware('forum'))
        ->add(RegisterMiddleware::class),
    (new Extend\Model(User::class))->hasMany('banned_ips', BannedIP::class),
    (new Extend\Model(Post::class))->hasOne('banned_ip',BannedIP::class, 'address', 'ip_address'),
    function (Dispatcher $events) {
        $events->subscribe(Access\UserPolicy::class);

        $events->subscribe(Listeners\AddApiAttributes::class);
        $events->subscribe(Listeners\RemoveAccessToBannedUsers::class);
    },
];
