<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Controller\AbstractSerializeController;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ServerRequestInterface;

class BannedIPData
{
    public function __construct(private BannedIPRepository $bannedIPs)
    {
    }

    /**
     * @param User $data
     *
     * @return User
     */
    public function __invoke(AbstractSerializeController $controller, &$data, ServerRequestInterface $request)
    {
        $canView = RequestUtil::getActor($request)->can('fof.ban-ips.viewBannedIPList');

        // Set as a loaded relation rather than an attribute. Assigning via array access
        // (`$data['banned_ips'] = ...`) would store it in the model's attributes, causing a
        // later `$data->save()` by another extension to attempt to persist a non-existent
        // `banned_ips` column.
        $data->setRelation('banned_ips', $canView ? $this->bannedIPs->getUserBannedIPs($data)->get() : new Collection());

        return $data;
    }
}
