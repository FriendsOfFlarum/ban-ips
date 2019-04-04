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
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;
use FoF\BanIPs\Commands\EditBannedIP;
use Tobscure\JsonApi\Document;

class UpdateBannedIPController extends AbstractShowController
{
    public $serializer = BanIPSerializer::class;
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
     * @param Document $document
     *
     * @return mixed
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        return $this->bus->dispatch(
            new EditBannedIP(array_get($request->getQueryParams(), 'id'), $request->getAttribute('actor'), array_get($request->getParsedBody(), 'data', []))
        );
    }
}
