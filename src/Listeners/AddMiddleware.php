<?php


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