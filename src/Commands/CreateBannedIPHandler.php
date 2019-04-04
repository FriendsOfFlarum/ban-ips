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

class CreateBannedIPHandler
{
    use AssertPermissionTrait;

    /**
     * @var BanIPValidator
     */
    protected $validator;

    /**
     * CreateRankHandler constructor.
     * @param BanIPValidator $validator
     */
    public function __construct(BanIPValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param CreateBannedIP $command
     * @return mixed
     * @throws PermissionDeniedException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(CreateBannedIP $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        $this->assertAdmin($actor);

        $banIP = BanIP::build(
            array_get($data, 'attributes.userID'),
            array_get($data, 'attributes.postID'),
            array_get($data, 'attributes.ipAddress')
        );

        $this->validator->assertValid($banIP->getAttributes());

        $banIP->save();

        return $banIP;
    }
}
