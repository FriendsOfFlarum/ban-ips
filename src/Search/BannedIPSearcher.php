<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Search;

use Flarum\Search\AbstractSearcher;
use Flarum\Search\GambitManager;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Database\Eloquent\Builder;

class BannedIPSearcher extends AbstractSearcher
{
    /**
     * @param GambitManager      $gambits
    
    public function __construct(protected BannedIPRepository $bannedIPs, GambitManager $gambits, array $searchMutators)
    {
        parent::__construct($gambits, $searchMutators);
    }

    public function getQuery(User $actor): Builder
    {
        return $this->bannedIPs->query();
    }
}
