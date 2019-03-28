<?php

namespace FoF\BanIPs\Access;

use Flarum\User\AbstractPolicy;
use Flarum\User\User;

class UserPolicy extends AbstractPolicy
{
    /**
     * {@inheritdoc}
     */
    protected $model = User::class;

    /**
     * @param User $actor
     * @param User $user
     *
     * @return bool|null
     */
    public function spamblock(User $actor, User $user)
    {
        if ($actor->id === $user->id || $user->can('user.ipblock')) {
            return false;
        }
    }
}
