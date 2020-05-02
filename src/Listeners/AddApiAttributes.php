<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Controller;
use Flarum\Api\Event\Serializing;
use Flarum\Api\Event\WillGetData;
use Flarum\Api\Event\WillSerializeData;
use Flarum\Api\Serializer\PostSerializer;
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Event\GetApiRelationship;
use Flarum\Event\GetModelRelationship;
use Flarum\Post\Post;
use Flarum\User\User;
use FoF\BanIPs\Api\Controllers\CheckIPsController;
use FoF\BanIPs\Api\Serializers\BannedIPSerializer;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Events\Dispatcher;

class AddApiAttributes
{
    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Serializing::class, [$this, 'addAttributes']);
        $events->listen(GetModelRelationship::class, [$this, 'addModelRelationship']);
        $events->listen(GetApiRelationship::class, [$this, 'addRelationship']);
        $events->listen(WillSerializeData::class, [$this, 'loadRelationship']);
        $events->listen(WillGetData::class, [$this, 'includeRelationship']);
    }

    public function addAttributes(Serializing $event)
    {
        if ($event->isSerializer(PostSerializer::class)) {
            $event->attributes['canBanIP'] = $event->actor->can('banIP', $event->model);
        }

        if ($event->isSerializer(UserSerializer::class)) {
            $event->attributes['isBanned'] = $this->bannedIPs->isUserBanned($event->model);
            $event->attributes['canBanIP'] = $event->actor->can('banIP', $event->model);
        }
    }

    public function addModelRelationship(GetModelRelationship $event)
    {
        if ($event->isRelationship(User::class, 'banned_ips')) {
            return $event->model->hasMany(BannedIP::class);
        }

        if ($event->isRelationship(Post::class, 'banned_ip')) {
            return $event->model->hasOne(BannedIP::class, 'address', 'ip_address');
        }
    }

    public function addRelationship(GetApiRelationship $event)
    {
        if ($event->isRelationship(UserSerializer::class, 'banned_ips')) {
            return $event->serializer->hasMany($event->model, BannedIPSerializer::class, 'banned_ips');
        }

        if ($event->isRelationship(PostSerializer::class, 'banned_ip')) {
            return $event->serializer->hasOne($event->model, BannedIPSerializer::class, 'banned_ip');
        }
    }

    /**
     * @param WillSerializeData $event
     */
    public function loadRelationship(WillSerializeData $event)
    {
        $canView = $event->actor->can('fof.ban-ips.viewBannedIPList');

        if ($event->isController(Controller\ShowUserController::class)
            || $event->isController(Controller\CreateUserController::class)
            || $event->isController(Controller\UpdateUserController::class)) {
            $event->data['banned_ips'] = $canView ? $this->bannedIPs->getUserBannedIPs($event->data)->get() : [];
        }

        if ($event->isController(Controller\ListUsersController::class)
            || $event->isController(CheckIPsController::class)) {
            foreach ($event->data as $data) {
                $data['banned_ips'] = $canView ? $this->bannedIPs->getUserBannedIPs($data)->get() : [];
            }
        }
    }

    /**
     * @param WillGetData $event
     */
    public function includeRelationship(WillGetData $event)
    {
        if ($event->isController(Controller\ListUsersController::class)
            || $event->isController(Controller\ShowUserController::class)
            || $event->isController(Controller\CreateUserController::class)
            || $event->isController(Controller\UpdateUserController::class)) {
            $event->addInclude('banned_ips');
            $event->addInclude('banned_ips.user');
        }

        if ($event->isController(Controller\ListPostsController::class)
            || $event->isController(Controller\ShowPostController::class)
            || $event->isController(Controller\CreatePostController::class)
            || $event->isController(Controller\UpdatePostController::class)
            || $event->isController(CheckIPsController::class)) {
            $event->addInclude('banned_ip');
            $event->addInclude('banned_ip.user');
        }
    }
}
