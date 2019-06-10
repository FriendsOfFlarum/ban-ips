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
use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use FoF\BanIPs\Api\Serializers\BannedIPSerializer;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ListUserBannedIPsController extends AbstractListController
{
    use AssertPermissionTrait;

    /**
     * {@inheritdoc}
     */
    public $include = ['user'];

    /**
     * {@inheritdoc}
     */
    public $optionalInclude = ['creator'];

    /**
     * {@inheritdoc}
     */
    public $serializer = BannedIPSerializer::class;

    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $this->assertCan($request->getAttribute('actor'), 'fof.banips.viewBannedIPList');

        $id = array_get($request->getQueryParams(), 'id');
        $user = User::where('id', $id)->orWhere('username', $id)->firstOrFail();

        return $this->bannedIPs->getUserBannedIPs($user)->get();
    }
}
