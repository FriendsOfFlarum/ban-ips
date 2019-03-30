<?php

namespace FoF\BanIPs;

use Flarum\Database\AbstractModel;
use Flarum\Post\Post;
use Flarum\User\User;

class BanIP extends AbstractModel
{
    /**
     * @var string
     */
    protected $table = 'banned_ip_addresses';
    /**
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     *
     */
    public function post()
    {
        $this->belongsTo(Post::class);
    }

    /**
     *
     */
    public function user()
    {
        $this->belongsTo(User::class);
    }
}
