<?php

namespace FoF\BanIPs\Commands;

use Flarum\User\User;

class DeleteBannedIP
{
    /**
     * @var int
     */
    public $bannedId;
    /**
     * @var User
     */
    public $actor;

    /**
     * @param int $bannedId
     * @param User $actor
     */
    public function __construct($bannedId, User $actor)
    {
        $this->bannedId = $bannedId;
        $this->actor = $actor;
    }
}
