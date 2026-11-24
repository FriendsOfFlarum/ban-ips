<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace FoF\BanIPs\Search;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Repositories\BannedIPRepository;

/**
 * @implements FilterInterface<DatabaseSearchState>
 */
class IPBannedFilter implements FilterInterface
{
    public function __construct(
        protected BannedIPRepository $bans
    ) {
    }

    public function getFilterKey(): string
    {
        return 'ipbanned';
    }

    public function filter(SearchState $state, string|array $value, bool $negate): void
    {
        if (!BannedIP::isVisibleTo($state->getActor())) {
            return;
        }

        $this->bans->whereBanned($state->getQuery(), !$negate);
    }
}
