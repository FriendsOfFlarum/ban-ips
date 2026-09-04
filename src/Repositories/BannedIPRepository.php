<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Repositories;

use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BannedIPRepository
{
    /**
     * @var array
     */
    private static $bans = [];

    /**
     * @var array
     */
    private static $ips = [];

    /**
     * Get a new query builder for the banned IP table.
     *
     * @return Builder
     */
    public function query()
    {
        return BannedIP::query();
    }

    /**
     * Find a banned IP address by ID.
     *
     * @param int  $id
     * @param User $actor
     *
     * @throws ModelNotFoundException
     *
     * @return BannedIP
     */
    public function findOrFail($id, ?User $actor = null)
    {
        $query = BannedIP::where('id', $id);

        /** @var BannedIP $bannedIP */
        $bannedIP = $this->scopeVisibleTo($query, $actor)->firstOrFail();

        return $bannedIP;
    }

    /**
     * Find by IP Address.
     *
     * @param string $ipAddress
     *
     * @return BannedIP|null
     */
    public function findByIPAddress($ipAddress)
    {
        if (empty($ipAddress)) {
            return null;
        }

        return BannedIP::where('address', $ipAddress)->first();
    }

    /**
     * @param User     $user
     * @param string[] $ips
     *
     * @return Collection
     */
    public function findOtherUsers(User $user, $ips)
    {
        if (empty($ips)) {
            return collect();
        }

        return $this->findUsers($ips)
            ->where('id', '!=', $user->id);
    }

    /**
     * @param array|string $ips
     *
     * @return Collection
     */
    public function findUsers($ips)
    {
        if (empty($ips)) {
            return collect();
        }

        // Select the distinct users who have posted from one of these IPs via a
        // subquery on `user_id`, rather than joining `posts` and using DISTINCT
        // over `users.*`. The latter forces the database to compare every user
        // column, which fails on PostgreSQL because the `preferences` json column
        // has no equality operator.
        return User::query()
            ->whereIn('id', function ($query) use ($ips) {
                $query->select('user_id')
                    ->from('posts')
                    ->whereIn('ip_address', Arr::wrap($ips));
            })
            ->get()
            ->filter(function (User $user) {
                return $user->cannot('banIP');
            });
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isUserBanned(User $user)
    {
        if (Arr::has(self::$bans, [$user->id])) {
            return (bool) self::$bans[$user->id];
        }

        // Use pre-loaded banned ips if available, otherwise run EXISTS query.
        /** @phpstan-ignore-next-line */
        $loadedIps = $user->relationLoaded('banned_ips') ? $user->banned_ips : null;

        $value = $user->cannot('banIP');

        if ($value) {
            /** @phpstan-ignore-next-line */
            $value = $value && ($loadedIps ? $loadedIps->isNotEmpty() : $user->banned_ips()->exists());
        }

        return self::$bans[$user->id] = $value;
    }

    public function getUserIPs(User $user): Collection
    {
        if (Arr::has(self::$ips, [$user->id])) {
            return self::$ips[$user->id];
        }

        /** @phpstan-ignore-next-line */
        return self::$ips[$user->id] = $user->post_ips->pluck('ip_address');
    }

    public function getUserBannedIPs(User $user): UserBannedIPs
    {
        /** @phpstan-ignore-next-line */
        return $user->banned_ips();
    }

    /**
     * Scope a query to only include records that are visible to a user.
     *
     * @param Builder $query
     * @param User    $actor
     *
     * @return Builder
     */
    protected function scopeVisibleTo(Builder $query, ?User $actor = null)
    {
        if ($actor !== null) {
            $query->whereVisibleTo($actor);
        }

        return $query;
    }
}
