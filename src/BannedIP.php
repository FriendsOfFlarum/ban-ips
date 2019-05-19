<?php

namespace FoF\BanIPs;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\User\User;
/**
 * @property User $creator
 * @property User $user
 * @property int $creator_id
 * @property int $user_id
 * @property string $address
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $deleted_at
 */
class BannedIP extends AbstractModel
{
    /**
     * @var string
     */
    protected $table = 'banned_ips';

    /**
     * @var array
     */
    protected $dates = ['created_at', 'deleted_at'];

    /**
     * @param $creatorId
     * @param $userId
     * @param $address
     * @param null $reason
     * @return BannedIP
     */
    public static function build($creatorId, $userId, $address, $reason = null)
    {
        $banIP = new static();

        $banIP->creator_id = $creatorId;
        $banIP->user_id = $userId;
        $banIP->address = $address;
        $banIP->reason = $reason;

        return $banIP;
    }

    public function creator()
    {
        $this->belongsTo(User::class);
    }

    public function user()
    {
        $this->belongsTo(User::class);
    }
}