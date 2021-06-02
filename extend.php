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

use Flarum\Api\Controller;
use Flarum\Api\Serializer;
use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Database\AbstractModel;
use Flarum\Extend;
use Flarum\Post\Post;
use Flarum\User\User;
use FoF\BanIPs\Api\Controllers\CheckIPsController;
use FoF\BanIPs\Api\Serializers\BannedIPSerializer;
use FoF\BanIPs\Middleware\RegisterMiddleware;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\Routes('api'))
        ->get('/fof/ban-ips/check-users', 'fof.ban-ips.check', Api\Controllers\CheckIPsController::class)
        ->get('/fof/ban-ips/check-users/{user}', 'fof.ban-ips.check.others', Api\Controllers\CheckIPsController::class)
        ->get('/fof/ban-ips', 'fof.ban-ips.index', Api\Controllers\ListBannedIPsController::class)
        ->post('/fof/ban-ips', 'fof.ban-ips.store', Api\Controllers\CreateBannedIPController::class)
        ->patch('/fof/ban-ips/{id}', 'fof.ban-ips.update', Api\Controllers\UpdateBannedIPController::class)
        ->delete('/fof/ban-ips/{id}', 'fof.ban-ips.delete', Api\Controllers\DeleteBannedIPController::class),

    (new Extend\Middleware('forum'))
        ->add(RegisterMiddleware::class),

    (new Extend\Model(Post::class))
        ->hasOne('banned_ip', BannedIP::class, 'address', 'ip_address'),

    (new Extend\Policy())
        ->modelPolicy(User::class, Access\UserPolicy::class),

    (new Extend\ApiSerializer(Serializer\PostSerializer::class))
        ->attributes(function (AbstractSerializer $serializer, AbstractModel $post, array $attributes): array {
            $attributes['canBanIP'] = $serializer->getActor()->can('banIP', $post);

            return $attributes;
        })
        ->hasOne('banned_ip', BannedIPSerializer::class),

    (new Extend\ApiController(CheckIPsController::class))
        ->prepareDataForSerialization(Listeners\BannedIPsData::class)
        ->addInclude(['banned_ip']),

    (new Extend\ApiController(Controller\ListPostsController::class))
        ->addInclude(['banned_ip']),

    (new Extend\ApiController(Controller\ShowPostsController::class))
        ->addInclude(['banned_ip']),

    (new Extend\ApiController(Controller\CreatePostController::class))
        ->addInclude(['banned_ip']),

    (new Extend\ApiController(Controller\UpdatePostController::class))
        ->addInclude(['banned_ip']),

    (new Extend\SimpleFlarumSearch(Search\BannedIPSearcher::class))
        ->setFullTextGambit(Search\NxGambit::class),

    (new Extend\Filter(Search\BannedIPFilterer::class))
        ->addFilter(Search\NxGambit::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(ForumAttributes::class),

    (new Extend\User())
        ->permissionGroups(RevokeAccessWhenIPBanned::class),
];
