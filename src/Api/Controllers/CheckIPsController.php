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
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Http\Exception\RouteNotFoundException;
use Flarum\User\AssertPermissionTrait;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class CheckIPsController extends AbstractListController
{
    use AssertPermissionTrait;

    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    /**
     * @var string
     */
    public $serializer = UserSerializer::class;

    /**
     * Get the data to be serialized and assigned to the response document.
     *
     * @param ServerRequestInterface $request
     * @param Document               $document
     *
     * @return mixed
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $this->assertCan($request->getAttribute('actor'), 'banIP');

        $userId = Arr::get($request->getQueryParams(), 'user');
        $user = $userId != null ? User::where('id', $userId)->orWhere('username', $userId)->first() : null;
        $ip = Arr::get($request->getQueryParams(), 'ip');

        $this->assertCan($request->getAttribute('actor'), 'banIP', $user);

        if (isset($ip) && $this->bannedIPs->findByIPAddress($ip) != null) {
            throw new PermissionDeniedException();
        }
        if (!isset($ip) && !isset($user)) {
            throw new RouteNotFoundException();
        }
        $ips = Arr::wrap(
            $ip ?? $user->posts()->whereNotNull('ip_address')->pluck('ip_address')->unique()
        );

        return $user != null
            ? $this->bannedIPs->findOtherUsers($user, $ips)
            : $this->bannedIPs->findUsers($ips);
    }
}
