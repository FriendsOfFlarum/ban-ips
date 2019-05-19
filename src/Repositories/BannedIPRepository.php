<?php


namespace FoF\BanIPs\Repositories;


use Flarum\Post\Post;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * @param int $id
     * @param User $actor
     *
     * @return BannedIP
     * @throws ModelNotFoundException
     *
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
     * @return BannedIP|null
     */
    public function findByIPAddress($ipAddress)
    {
        return BannedIP::where('address', $ipAddress)->first();
    }

    /**
     * @param User $user
     * @param string[] $ips
     * @return array
     */
    public function findOtherUsers(User $user, $ips) {
        if (empty($ips)) return [];

        return Post::whereIn('ip_address', $ips)
            ->where('user_id', '!=', $user->id)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(function (User $user) {
                return $user->cannot('banIP');
            })
            ->unique();
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