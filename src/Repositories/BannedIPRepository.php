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
     * Users about to be serialized, answered together by the first isUserBanned().
     *
     * @var array<int, User>
     */
    private static $queue = [];

    /**
     * The permission that lets a user ban IPs, which also exempts them from IP bans.
     */
    private const BAN_PERMISSION = 'fof.ban-ips.banIP';

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
        return User::join('posts', function ($join) use ($ips) {
            $join->on('users.id', '=', 'posts.user_id')
                    ->whereIn('posts.ip_address', Arr::wrap($ips));
        })
            ->select('users.*', 'posts.ip_address')
            ->distinct()
            ->get()
            ->filter(function (User $user) {
                return !$this->isExempt($user);
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

        if (isset(self::$queue[$user->id])) {
            $this->loadQueue();

            return (bool) self::$bans[$user->id];
        }

        return self::$bans[$user->id] = !$this->isExempt($user) && $this->getUserBannedIPs($user)->exists();
    }

    /**
     * Each user's banned IPs (bans on the user, or on an address they posted from),
     * for many users in two queries instead of two per user.
     *
     * @param iterable<User> $users
     *
     * @return array<int, \Illuminate\Database\Eloquent\Collection<BannedIP>> keyed by user id
     */
    public function getBannedIPsForUsers(iterable $users): array
    {
        $result = [];
        foreach ($users as $user) {
            $result[$user->id] = new \Illuminate\Database\Eloquent\Collection();
        }

        if (empty($result)) {
            return $result;
        }

        $ids = array_keys($result);

        $byUser = BannedIP::query()->whereIn('user_id', $ids)->get();
        foreach ($byUser as $bannedIP) {
            $result[$bannedIP->user_id]->put($bannedIP->id, $bannedIP);
        }

        $byAddress = BannedIP::query()
            ->join('posts', 'posts.ip_address', '=', 'banned_ips.address')
            ->whereIn('posts.user_id', $ids)
            ->select('banned_ips.*', 'posts.user_id as matched_user_id')
            ->distinct()
            ->get();
        foreach ($byAddress as $bannedIP) {
            $userId = (int) $bannedIP->getAttribute('matched_user_id');
            unset($bannedIP['matched_user_id']);
            $result[$userId]->put($bannedIP->id, $bannedIP);
        }

        $all = new \Illuminate\Database\Eloquent\Collection();
        foreach ($result as $userId => $bannedIPs) {
            $result[$userId] = $bannedIPs->values();
            $all = $all->merge($result[$userId]);
        }

        // The ban's user is included in the response; load them together too.
        $all->load('user');

        return $result;
    }

    /**
     * Queue the users of a response so that isUserBanned() answers for all of them
     * at once. Nothing is queried unless isUserBanned() is asked.
     *
     * @param User[] $users
     */
    public function queue(array $users): void
    {
        self::$queue = [];

        foreach ($users as $user) {
            if ($user->exists && !Arr::has(self::$bans, [$user->id])) {
                self::$queue[$user->id] = $user;
            }
        }
    }

    /**
     * Two queries for the whole queue: bans on the users themselves, and bans on
     * addresses they posted from (matched in the database). Only users who match a
     * ban need their own permissions worked out, to see whether they are exempt.
     */
    protected function loadQueue(): void
    {
        $users = self::$queue;
        self::$queue = [];
        $ids = array_keys($users);

        $byUser = BannedIP::query()->whereIn('user_id', $ids)->pluck('user_id');
        $byAddress = BannedIP::query()
            ->join('posts', 'posts.ip_address', '=', 'banned_ips.address')
            ->whereIn('posts.user_id', $ids)
            ->distinct()
            ->pluck('posts.user_id');

        $matched = $byUser->merge($byAddress)->map(function ($id) {
            return (int) $id;
        })->flip();

        foreach ($users as $id => $user) {
            self::$bans[$id] = isset($matched[$id]) && !$this->isExempt($user);
        }
    }

    /**
     * Users who may ban IPs are never treated as banned. (Admins hold every permission.).
     */
    protected function isExempt(User $user): bool
    {
        return $user->hasPermission(self::BAN_PERMISSION);
    }

    public function getUserIPs(User $user): Collection
    {
        if (Arr::has(self::$ips, [$user->id])) {
            return self::$ips[$user->id];
        }

        return self::$ips[$user->id] = $user->posts()->whereNotNull('ip_address')->pluck('ip_address')->unique();
    }

    public function getUserBannedIPs(User $user): Builder
    {
        $ips = $this->getUserIPs($user)->toArray();

        return BannedIP::where('user_id', $user->id)->orWhere('address', empty($ips) ? null : $this->getUserIPs($user)->toArray());
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
