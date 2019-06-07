<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Repositories;

use Flarum\Post\Post;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BannedIPRepository
{
    /**
     * Get a new query builder for the pages table.
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
    public function findOrFail($id, User $actor = null)
    {
        $query = BannedIP::where('id', $id);

        return $this->scopeVisibleTo($query, $actor)->firstOrFail();
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
            return [];
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
        return User
            ::join('posts', function ($join) use ($ips) {
                $join->on('users.id', '=', 'posts.user_id')
                    ->whereIn('posts.ip_address', Arr::wrap($ips));
            })
            ->select('users.*', 'posts.ip_address')
            ->distinct()
            ->get()
            ->filter(function (User $user) {
                return $user->cannot('banIP');
            });
    }

    /**
     * @param User $user
     */
    public function isUserBanned(User $user)
    {
        return $user->cannot('banIP') && $this->getUserBannedIPs($user)->exists();
    }

    public function getUserIPs(User $user)
    {
        return $user->posts()->whereNotNull('ip_address')->pluck('ip_address')->unique();
    }

    public function getUserBannedIPs(User $user) : Builder
    {
        return BannedIP::where('address', $this->getUserIPs($user)->toArray());
    }

    /**
     * Scope a query to only include records that are visible to a user.
     *
     * @param Builder $query
     * @param User    $actor
     *
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
