<?php


namespace FoF\BanIPs\Api\Controllers;


use Flarum\Api\Controller\AbstractListController;
use FoF\BanIPs\Api\Serializers\BannedIPSerializer;
use FoF\BanIPs\Commands\CreateBannedIPs;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class CreateBannedIPsController extends AbstractListController
{
    /**
     * @var string
     */
    public $serializer = BannedIPSerializer::class;

    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * CreateBannedIPController constructor.
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Get the data to be serialized and assigned to the response document.
     *
     * @param ServerRequestInterface $request
     * @param Document $document
     * @return mixed
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        return $this->bus->dispatch(
            new CreateBannedIPs($request->getAttribute('actor'), array_get($request->getParsedBody(), 'data'))
        );
    }
}