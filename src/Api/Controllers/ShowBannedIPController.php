<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

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
