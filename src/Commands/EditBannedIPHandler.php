<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Commands;

use Flarum\Http\Exception\RouteNotFoundException;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Validators\BannedIPValidator;
use Illuminate\Support\Arr;

class EditBannedIPHandler
{
    /**
     * @var BannedIPValidator
     */
    private $validator;

    /**
     * @param BannedIPValidator $validator
     */
    public function __construct(BannedIPValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param EditBannedIP $command
     *
     * @return BannedIP
     */
    public function handle(EditBannedIP $command)
    {
        /**
         * @var User
         */
        $actor = $command->actor;
        $data = $command->data;

        $attributes = Arr::get($data, 'attributes', []);

        /** @var BannedIP $bannedIP */
        $bannedIP = BannedIP::find($command->bannedId);

        $actor->assertCan('banIP');

        if ($bannedIP !== null && $actor === $bannedIP->creator) {
            throw new PermissionDeniedException();
        } elseif ($bannedIP == null) {
            throw new RouteNotFoundException();
        }

        if (isset($attributes['userId'])) {
            $bannedIP->user_id = $attributes['userId'];
        }

        if (isset($attributes['ipAddress'])) {
            $bannedIP->address = $attributes['ipAddress'];
        }

        if (isset($attributes['reason'])) {
            $bannedIP->reason = $attributes['reason'];
        }

        $this->validator->assertValid($bannedIP->getDirty());

        if ($bannedIP->isDirty()) {
            $bannedIP->save();
        }

        return $bannedIP;
    }
}
