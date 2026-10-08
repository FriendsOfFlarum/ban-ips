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
     * @var array
     */
    private static $bans = [];

    /**
     * @var array
     */
    private static $ips = [];

    /**
     * Users whose ban status will be read soon, keyed by id.
     *
     * @var array<int, User>
     */
    private static array $queued = [];

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

        // Queued alongside others (see queue()): answer them all at once.
        if (isset(self::$queued[$user->id])) {
            $this->loadQueued();

            return (bool) self::$bans[$user->id];
        }

        return self::$bans[$user->id] = $user->cannot('banIP') && $this->getUserBannedIPs($user)->exists();
    }

    /**
     * Ask about a user later, together with every other user queued before the
     * first answer is read.
     *
     * Serializing a page of users (the authors on a discussion list, a post
     * stream, the user directory) used to cost two queries per user: one
     * loading every IP address they had ever posted from, one checking those
     * against the bans. Queued, the whole page is answered in two queries, and
     * the addresses never leave the database.
     */
    public function queue(User $user): void
    {
        if (!Arr::has(self::$bans, [$user->id])) {
            self::$queued[$user->id] = $user;
        }
    }

    private function loadQueued(): void
    {
        $users = self::$queued;
        self::$queued = [];

        // Users who may ban IPs are never treated as banned.
        $candidates = array_keys(array_filter($users, fn (User $user) => $user->cannot('banIP')));

        foreach (array_keys($users) as $id) {
            self::$bans[$id] = false;
        }

        if ($candidates === []) {
            return;
        }

        // Banned directly, or posted from a banned address.
        $banned = BannedIP::query()->whereIn('user_id', $candidates)->pluck('user_id')
            ->merge(
                Post::query()
                    ->join('banned_ips', 'banned_ips.address', '=', 'posts.ip_address')
                    ->whereIn('posts.user_id', $candidates)
                    ->distinct()
                    ->pluck('posts.user_id')
            );

        foreach ($banned as $id) {
            self::$bans[(int) $id] = true;
        }
    }

    /**
     * Forget every answer and queued user. For long-running processes and tests.
     */
    public static function resetCache(): void
    {
        self::$bans = [];
        self::$ips = [];
        self::$queued = [];
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
