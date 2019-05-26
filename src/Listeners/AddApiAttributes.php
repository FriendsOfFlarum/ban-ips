<?php

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Controller\ShowForumController;
use Flarum\Api\Event\Serializing;
use Flarum\Api\Event\WillGetData;
use Flarum\Api\Event\WillSerializeData;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Api\Serializer\PostSerializer;
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Event\GetApiRelationship;
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
        }
    }

    public function addRelationship(GetApiRelationship $event) {
        if ($event->isRelationship(ForumSerializer::class, 'banned_ips')) {
            return $event->serializer->hasMany($event->model, BannedIPSerializer::class, 'banned_ips');
        }
    }

    /**
     * @param WillSerializeData $event
     */
    public function loadRelationship(WillSerializeData $event)
    {
        if ($event->isController(ShowForumController::class)) {
            $event->data['banned_ips'] = $event->actor->can('fof.ban-ips.viewBannedIPList') ? BannedIP::get() : [];
        }
    }

    /**
     * @param WillGetData $event
     */
    public function includeRelationship(WillGetData $event)
    {
        if ($event->isController(ShowForumController::class)) {
            $event->addInclude('banned_ips');
            $event->addInclude('banned_ips.creator');
            $event->addInclude('banned_ips.user');
        }
    }
}