<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Search;

use Flarum\Search\ApplySearchParametersTrait;
use Flarum\Search\GambitManager;
use Flarum\Search\SearchCriteria;
use Flarum\Search\SearchResults;
use FoF\BanIPs\Repositories\BannedIPRepository;

class BannedIPSearcher
{
    use ApplySearchParametersTrait;

    /**
     * @var GambitManager
     */
    protected $gambits;

    /**
     * @var BannedIPRepository
     */
    protected $bannedIPs;

    /**
     * @param GambitManager      $gambits
     * @param BannedIPRepository $bannedIPs
     */
    public function __construct(GambitManager $gambits, BannedIPRepository $bannedIPs)
    {
        $this->gambits = $gambits;
        $this->bannedIPs = $bannedIPs;
    }

    /**
     * @param SearchCriteria $criteria
     * @param int|null       $limit
     * @param int            $offset
     *
     * @return SearchResults
     */
    public function search(SearchCriteria $criteria, $limit = null, $offset = 0)
    {
        $actor = $criteria->actor;

        $query = $this->bannedIPs->query();

        if ($actor !== null && !$actor->isAdmin()) {
            $query->whereIsHidden(0);
        }

        $search = new BannedIPSearch($query->getQuery(), $actor);

        $this->gambits->apply($search, $criteria->query);

        $this->applySort($search, $criteria->sort);
        $this->applyOffset($search, $offset);
        $this->applyLimit($search, $limit + 1);

        $bannedIPs = $query->get();

        if ($areMoreResults = ($limit > 0 && $bannedIPs->count() > $limit)) {
            $bannedIPs->pop();
        }

        return new SearchResults($bannedIPs, $areMoreResults);
    }
}
