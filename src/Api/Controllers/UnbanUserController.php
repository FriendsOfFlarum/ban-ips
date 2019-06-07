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

use Flarum\Api\Controller\AbstractDeleteController;
use FoF\BanIPs\Api\Serializers\BannedIPSerializer;
use FoF\BanIPs\Commands\UnbanUser;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;

class UnbanUserController extends AbstractDeleteController
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
        return $this->bus->dispatch(
            new UnbanUser($request->getAttribute('actor'), array_get($request->getQueryParams(), 'id'))
        );
    }
}
