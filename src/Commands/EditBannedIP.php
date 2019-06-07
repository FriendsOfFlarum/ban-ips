<?php


namespace FoF\BanIPs\Commands;


use Flarum\User\User;

class EditBannedIP
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
     * @var array
     */
    public $data;

    /**
     * @param int $bannedId
     * @param User $actor
     * @param array $data
     */
    public function __construct(User $actor, int $bannedId, array $data)
    {
        $this->actor = $actor;
        $this->bannedId = $bannedId;
        $this->data = $data;
    }
}