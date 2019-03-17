<?php

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Api\Event\Serializing;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

class InjectSettings
{
    protected $settings;
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Serializing::class, [$this, 'permissions']);
    }
    public function permissions(Serializing $event)
    {
        if ($event->serializer instanceof ForumSerializer) {
            $event->attributes['fof-ban-ips.ips'] = $this->settings->get('fof-ban-ips.ips', '[]');
        }
    }
}