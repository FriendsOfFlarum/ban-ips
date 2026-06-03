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

use Flarum\Search\Database\AbstractSearcher;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use Illuminate\Database\Eloquent\Builder;

/**
 * Registers the banned IP model with the database search driver. We do not ship
 * any fulltext gambit or filters of our own; the searcher exists so that the
 * listing endpoint is paginated through the search pipeline and so other
 * extensions can hook in their own filters.
 */
class BannedIPSearcher extends AbstractSearcher
{
    public function getQuery(User $actor): Builder
    {
        return BannedIP::query();
    }
}
