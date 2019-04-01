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

use Flarum\Api\Event\Serializing;
use Flarum\Api\Serializer\PostSerializer;
use Illuminate\Contracts\Events\Dispatcher;

class AddApiAttributes
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Serializing::class, [$this, 'addAttributes']);
    }

    public function addAttributes(Serializing $event)
    {
        if ($event->isSerializer(PostSerializer::class)) {
            $event->attributes['canBanIP'] =  $event->actor->can('banIP', $event->model);
        }
    }

}
