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

use Flarum\Api\Controller\AbstractListController;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Database\Eloquent\Collection;
use Psr\Http\Message\ServerRequestInterface;

class BannedIPsData
{
    public function __construct(private BannedIPRepository $bannedIPs)
    {
    }

    /**
     * @param iterable<User> $data
     *
     * @return iterable<User>
     */
    public function __invoke(AbstractListController $controller, &$data, ServerRequestInterface $request)
    {
        $canView = RequestUtil::getActor($request)->can('fof.ban-ips.viewBannedIPList');

        // Set as a loaded relation rather than an attribute. Assigning via array access
        // (`$d['banned_ips'] = ...`) would store it in the model's attributes, causing a
        // later `$d->save()` by another extension to attempt to persist a non-existent
        // `banned_ips` column.
        foreach ($data as $d) {
            $d->setRelation('banned_ips', $canView ? $this->bannedIPs->getUserBannedIPs($d)->get() : new Collection());
        }

        return $data;
    }
}
