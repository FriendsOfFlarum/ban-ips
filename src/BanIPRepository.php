<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs;

use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder;

class BanIPRepository
{
    /**
     * Get a new query builder for the pages table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return BanIP::query();
    }

    /**
     * Find a banned IP address by ID.
     *
     * @param int $id
     * @param User $actor
     *
     * @return BanIP
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     */
    public function findOrFail($id, User $actor = null)
    {
        $query = BanIP::where('id', $id);

        return $this->scopeVisibleTo($query, $actor)->firstOrFail();
    }

    /**
     * Find by IP Address.
     *
     * @param string $ipAddress
     * @return User|null
     */
    public function findByIPAddress($ipAddress)
    {
        return BanIP::where('ip_address', $ipAddress)->first();
    }

    /**
     * Scope a query to only include records that are visible to a user.
     *
     * @param Builder $query
     * @param User $actor
     * @return Builder
     */
    protected function scopeVisibleTo(Builder $query, User $actor = null)
    {
        if ($actor !== null) {
            $query->whereVisibleTo($actor);
        }
        return $query;
    }
}
