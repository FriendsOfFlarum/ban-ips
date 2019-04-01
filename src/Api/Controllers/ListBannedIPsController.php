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

use Flarum\Api\Controller\AbstractListController;
use Flarum\User\Exception\PermissionDeniedException;
use FoF\BanIPs\Api\Serializers\BanIPSerializer;
use FoF\BanIPs\BanIP;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tobscure\JsonApi\Document;

class ListBannedIPsController extends AbstractListController
{
    /**
     * @var string
     */
    public $serializer = BanIPSerializer::class;

    /**
     * @param Request $request
     * @param Document $document
     * @return BanIP[]|\Illuminate\Database\Eloquent\Collection|mixed
     * @throws PermissionDeniedException
     */
    protected function data(Request $request, Document $document)
    {
        if ($request->getAttribute('actor')->cannot('fof.banips.viewBannedIPList')) {
            throw new PermissionDeniedException();
        }
        return BanIP::all();
    }
}
