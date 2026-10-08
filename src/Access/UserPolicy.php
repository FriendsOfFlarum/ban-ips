<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class UserPolicy extends AbstractPolicy
{
    private string $key = 'fof.ban-ips.banIP';

    /**
     * @param User  $actor
     * @param ?User $user
     *
     * @return bool|string
     */
    public function banIP(User $actor, ?User $user)
    {
        // The actor first: most can't ban anyone, and asking whether the user
        // could ban IPs themselves loads that user's groups and permissions,
        // for every user on the page.
        if (!$actor->hasPermission($this->key)) {
            return false;
        }

        if (!$user->isGuest() && ($actor->id === $user->id || $user->hasPermission($this->key))) {
            return $this->deny();
        }

        return true;
    }
}
