<?php

namespace FoF\BanIPs\Api\Controller;

use Flarum\Api\Controller\AbstractListController;
use FoF\BanIPs\Api\Serializer\BanIPSerializer;
use FoF\BanIPs\BanIP;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tobscure\JsonApi\Document;

class ListBannedIPsController extends AbstractListController
{
    public $serializer = BanIPSerializer::class;

    protected function data(Request $request, Document $document)
    {
        return BanIP::all();
    }
}
