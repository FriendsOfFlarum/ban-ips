<?php


namespace FoF\BanIPs\Api\Controllers;

use Flarum\Api\Controller\AbstractDeleteController;
use FoF\BanIPs\Commands\DeleteBannedIP;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;

class DeleteBannedIPController extends AbstractDeleteController
{
    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function delete(ServerRequestInterface $request)
    {
        $this->bus->dispatch(
            new DeleteBannedIP($request->getAttribute('actor'), array_get($request->getQueryParams(), 'id'))
        );
    }
}