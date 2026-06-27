<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * We use a custom relation due to the fact that a user can have banned IPs not associated with it.
 * We need special logic for eager-loading and properly querying the user's post IPs to check for bans.
 * If we do a simpler ->hasMany()->orWhereIn(), eager-loading doesn't work since $user->id is NULL.
 */
class UserBannedIps extends HasMany
{
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query->where($this->foreignKey, '=', $this->getParentKey());

            // If not loaded, load it on the single parent model.
            // (loadMissing handles both collections and single models).
            $this->parent->loadMissing('post_ips');

            $ips = $this->parent->post_ips->pluck('ip_address');

            if ($ips->isNotEmpty()) {
                $this->query->orWhereIn('address', $ips);
            }
        }
    }

    public function addEagerConstraints(array $models)
    {
        $userIds = $this->getKeys($models, $this->localKey);

        // Dynamically batch-load post_ips only for models that don't have it cached.
        // This is N+1 safe as it executes a single query for all missing models.
        Collection::make($models)->loadMissing('post_ips');

        // Now post_ips is guaranteed to be cached on all models in the array
        $ips = collect($models)->flatMap(function ($model) {
            return $model->post_ips->pluck('ip_address');
        })->filter()->unique();

        $this->query->where(function ($query) use ($userIds, $ips) {
            $query->whereIn($this->foreignKey, $userIds);

            if ($ips->isNotEmpty()) {
                $query->orWhereIn('address', $ips);
            }
        });
    }

    /**
     * For matching retrieved records back to parent models.
     */
    public function match(array $models, Collection $results, $relation)
    {
        foreach ($models as $model) {
            $matched = $results->filter(function ($bannedIp) use ($model) {
                if ($bannedIp->user_id == $model->id) {
                    return true;
                }

                // Since we ensured post_ips is loaded in addEagerConstraints,
                // we can safely read it directly here.
                return $model->post_ips->pluck('ip_address')->contains($bannedIp->address);
            });

            $model->setRelation($relation, $matched);
        }

        return $models;
    }
}
