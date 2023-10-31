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
    private $key = 'fof.ban-ips.banIP';

    /**
     * @param User  $actor
     * @param ?User $user
     *
     * @return bool|null
     */
    public function banIP(User $actor, ?User $user)
    {
        if (!$user->isGuest() && ($actor->id === $user->id || $user->hasPermission($this->key))) {
            return $this->deny();
        }

        return $actor->hasPermission($this->key);
    }
}
