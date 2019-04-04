<?php

namespace FoF\BanIPs\Api\Controllers;

use Flarum\Api\Controller\AbstractShowController;
use FoF\BanIPs\Api\Serializers\BanIPSerializer;
use FoF\BanIPs\BanIP;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tobscure\JsonApi\Document;

class ShowBannedIPController extends AbstractShowController
{
    /**
     * @var string
     */
    public $serializer = BanIPSerializer::class;

    /**
     * @param Request $request
     * @param Document $document
     * @return mixed
     */
    protected function data(Request $request, Document $document)
    {
        $id = array_get($request->getQueryParams(), 'id');

        return BanIP::findOrFail($id);
    }
}
