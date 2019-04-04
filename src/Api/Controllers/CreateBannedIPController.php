<?php

namespace FoF\BanIPs\Api\Controllers;

use Flarum\Api\Controller\AbstractCreateController;
use FoF\BanIPs\Api\Serializers\BanIPSerializer;
use FoF\BanIPs\BanIP;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tobscure\JsonApi\Document;

class CreateBannedIPController extends AbstractCreateController
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
        $attributes = array_get($request->getParsedBody(), 'data.attributes');

        return BanIP::create([
            'post_id' => array_get($attributes, 'postID'),
            'user_id' => array_get($attributes, 'userID'),
            'ip_address' => array_get($attributes, 'ipAddress', "::1")
        ]);
    }
}
