<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Commands;

use Flarum\User\AssertPermissionTrait;
use Flarum\User\Exception\PermissionDeniedException;
use FoF\BanIPs\BanIP;
use FoF\BanIPs\Validators\BanIPValidator;

class EditBannedIPHandler
{
    use AssertPermissionTrait;

    /**
     * @var BanIPValidator
     */
    protected $validator;
    /**
     * @param BanIPValidator $validator
     */
    public function __construct(BanIPValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param EditBannedIP $command
     * @return mixed
     * @throws PermissionDeniedException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(EditBannedIP $command)
    {
        $actor = $command->actor;
        $data = $command->data;
        $attributes = array_get($data, 'attributes', []);

        $validate = [];

        $this->assertAdmin($actor);

        $banIP = BanIP::where('id', $command->banId)->firstOrFail();

        if (isset($attributes['userID']) && '' !== $attributes['userID']) {
            $validate['userID'] = $attributes['userID'];
            $banIP->updateUserID($attributes['userID']);
        }
        if (isset($attributes['postID']) && '' !== $attributes['postID']) {
            $validate['postID'] = $attributes['postID'];
            $banIP->updatePostID($attributes['postID']);
        }
        if (isset($attributes['ipAddress']) && '' !== $attributes['ipAddress']) {
            $validate['ipAddress'] = $attributes['ipAddress'];
            $banIP->updateIPAddress($attributes['ipAddress']);
        }
        $this->validator->assertValid(array_merge($banIP->getDirty(), $validate));

        $banIP->save();

        return $banIP;
    }
}
