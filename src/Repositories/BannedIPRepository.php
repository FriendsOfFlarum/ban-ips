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

use Flarum\Group\Group;
use Flarum\Group\Permission;
use Flarum\Post\Post;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
                return !$this->exempt($user);
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

        return self::$bans[$user->id] = !$this->exempt($user) && (
            $this->usersBannedDirectly()->where('user_id', $user->id)->exists()
            || $this->usersBannedByAddress()->where('posts.user_id', $user->id)->exists()
        );
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

        foreach (array_keys($users) as $id) {
            self::$bans[$id] = false;
        }

        $banned = $this->matchingUserIds(array_keys($users));

        // Users who may ban IPs are never treated as banned (see exempt()).
        // Asked only of the few who matched a ban: it means working out a
        // user's permissions, which loads their groups and runs every
        // permission group processor.
        $matched = array_values(array_intersect_key($users, array_flip($banned->all())));
        (new EloquentCollection($matched))->loadMissing('groups');

        foreach ($matched as $user) {
            self::$bans[$user->id] = !$this->exempt($user);
        }
    }

    /**
     * Narrow a users query to those who are IP-banned, or to everyone else.
     *
     * Matched with subqueries, so matched users aren't loaded unless they may
     * be exempt.
     */
    public function whereBanned(Builder $query, bool $banned = true): void
    {
        $isBanned = function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->whereIn('users.id', $this->usersBannedDirectly())
                    ->orWhereIn('users.id', $this->usersBannedByAddress());
            });

            $this->whereNotExempt($query);
        };

        $banned ? $query->where($isBanned) : $query->whereNot($isBanned);
    }

    /**
     * Leave out the users exempt from bans (see exempt()), going by the groups
     * that may ban IPs. Groups added by permission group processors are not
     * seen here.
     */
    private function whereNotExempt(Builder $query): void
    {
        $groupIds = Permission::query()->where('permission', 'fof.ban-ips.banIP')->pluck('group_id')->all();

        if (in_array(Group::GUEST_ID, $groupIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        // Every confirmed user is a member.
        if (in_array(Group::MEMBER_ID, $groupIds)) {
            $query->where('users.is_email_confirmed', false);
        }

        $candidates = User::query()
            ->where(function (Builder $query) {
                $query->whereIn('id', $this->usersBannedDirectly())
                    ->orWhereIn('id', $this->usersBannedByAddress());
            })
            ->whereHas('groups', fn (Builder $query) => $query->whereIn('groups.id', [...$groupIds, Group::ADMINISTRATOR_ID]));

        $exempt = $candidates->with('groups')->get()
            ->filter(fn (User $user) => $this->exempt($user))
            ->map(fn (User $user) => (int) $user->id)
            ->values()
            ->all();

        $query->whereNotIn('users.id', $exempt);
    }

    /**
     * The users a ban matches, among the given ones, before exemptions.
     *
     * @param list<int> $userIds
     *
     * @return Collection<int, int>
     */
    private function matchingUserIds(array $userIds): Collection
    {
        return $this->usersBannedDirectly()->whereIn('user_id', $userIds)->pluck('user_id')
            ->merge($this->usersBannedByAddress()->whereIn('posts.user_id', $userIds)->distinct()->pluck('user_id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /**
     * Users with a ban made for them. With usersBannedByAddress(), what makes
     * a user banned for every check.
     */
    private function usersBannedDirectly(): QueryBuilder
    {
        return BannedIP::query()->toBase()->select('user_id')->whereNotNull('user_id');
    }

    /**
     * Users who posted from a banned address.
     */
    private function usersBannedByAddress(): QueryBuilder
    {
        return Post::query()->toBase()
            ->select('posts.user_id')
            ->join('banned_ips', 'banned_ips.address', '=', 'posts.ip_address')
            ->whereNotNull('posts.user_id');
    }

    /**
     * Users who may ban IPs are never treated as banned.
     *
     * Asked of the user's own permissions: `$user->cannot('banIP')`, with no
     * model, matches no policy and falls back to a permission literally named
     * `banIP`, which only admins have. Moderators granted
     * `fof.ban-ips.banIP` were treated as banned, and logged out, after
     * posting from a banned address.
     */
    private function exempt(User $user): bool
    {
        return $user->hasPermission('fof.ban-ips.banIP');
    }

    /**
     * Forget every answer and queued user, for tests that check users one by one.
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
        return BannedIP::query()->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereIn('address', Post::query()->select('ip_address')->where('user_id', $user->id)->whereNotNull('ip_address'));
        });
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
