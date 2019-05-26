<?php


namespace FoF\BanIPs\Events;


use Flarum\User\User;
use FoF\BanIPs\BannedIP;

class IPWasBanned
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var BannedIP
     */
    public $bannedIP;

    /**
     * PollWasCreated constructor.
     *
     * @param BannedIP $bannedIP
     * @param User       $actor
     */
    public function __construct(BannedIP $bannedIP, User $actor)
    {
        $this->bannedIP = $bannedIP;
        $this->actor = $actor;
    }
}