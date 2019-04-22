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
use FoF\BanIPs\BanIP;
use FoF\BanIPs\BanIPValidator;

class CreateBannedIPHandler
{
    use AssertPermissionTrait;

    /**
     * @var BanIPValidator
     */
    protected $validator;

    /**
     * CreateBannedIPHandler constructor.
     * @param BanIPValidator $validator
     */
    public function __construct(BanIPValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param CreateBannedIP $command
     * @return mixed
     * @throws \Flarum\User\Exception\PermissionDeniedException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(CreateBannedIP $command)
    {
        $actor = $command->actor;
        $data = $command->data;

        $this->assertCan($actor, 'fof.banips.banIP');

        $banIP = BanIP::build(
            array_get($data, 'attributes.userId'),
            array_get($data, 'attributes.postId'),
            array_get($data, 'attributes.ipAddress')
        );

        $this->validator->assertValid($banIP->getAttributes());

        $banIP->save();

        return $banIP;
    }
}
