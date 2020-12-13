<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Controller\AbstractSerializeController;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Psr\Http\Message\ServerRequestInterface;

class BannedIPData
{
    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    public function __invoke(AbstractSerializeController $controller, &$data, ServerRequestInterface $request)
    {
        $canView = $request->getAttribute('actor')->can('fof.ban-ips.viewBannedIPList');

        $data['banned_ips'] = $canView ? $this->bannedIPs->getUserBannedIPs($data)->get() : [];

        return $data;
    }
}
