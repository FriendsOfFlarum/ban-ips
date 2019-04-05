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
use FoF\BanIPs\BanIPRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tobscure\JsonApi\Document;

class ShowBannedIPController extends AbstractShowController
{
    /**
     * @var string
     */
    public $serializer = 'FoF\BanIPs\Api\Serializers\BanIPSerializer';

    protected $banIP;

    public function __construct(BanIPRepository $banIP)
    {
        $this->banIP = $banIP;
    }

    protected function data(Request $request, Document $document)
    {
        $id = array_get($request->getQueryParams(), 'id');

        $actor = $request->getAttribute('actor');

        return $this->banIP->findOrFail($id, $actor);
    }
}
