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

use Flarum\Api\Controller\AbstractCreateController;
use FoF\BanIPs\Commands\CreateBannedIP;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tobscure\JsonApi\Document;

class CreateBannedIPController extends AbstractCreateController
{
    /**
     * @var string
     */
    public $serializer = 'FoF\BanIPs\Api\Serializers\BanIPSerializer';

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
     * @param Request $request
     * @param Document $document
     * @return mixed
     */
    protected function data(Request $request, Document $document)
    {
        return $this->bus->dispatch(
            new CreateBannedIP($request->getAttribute('actor'), array_get($request->getParsedBody(), 'data'))
        );
    }
}
