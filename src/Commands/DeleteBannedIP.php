<?php


namespace FoF\BanIPs\Commands;


use Flarum\User\User;

class DeleteBannedIP
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var int
     */
    public $bannedId;

    /**
     * @param int $bannedId
     * @param User $actor
     */
    public function __construct(User $actor, int $bannedId)
    {
        $this->actor = $actor;
        $this->bannedId = $bannedId;
    }
}