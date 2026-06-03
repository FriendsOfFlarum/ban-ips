<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Tests\integration;

use Carbon\Carbon;
use Flarum\Api\Controller\AbstractListController;
use Flarum\Api\Controller\AbstractSerializeController;
use Flarum\Http\RequestUtil;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\BanIPs\Listeners\BannedIPData;
use FoF\BanIPs\Listeners\BannedIPsData;
use FoF\BanIPs\Tests\fixtures\IPAddressesTrait;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class SerializationDataTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use IPAddressesTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $this->prepareDatabase([
            // The fixture associates every banned IP with user_id 3.
            'banned_ips' => $this->getBannedIPsForDB(),
            'users'      => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'ipBanned', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'ipbanned@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
        ]);
    }

    protected function requestAsAdmin(): ServerRequestInterface
    {
        $this->app();

        return RequestUtil::withActor(new ServerRequest([], [], null, 'GET'), User::find(1));
    }

    /**
     * @test
     */
    public function show_user_listener_sets_banned_ips_as_a_relation_not_an_attribute()
    {
        $request = $this->requestAsAdmin();
        $listener = $this->app()->getContainer()->make(BannedIPData::class);

        $user = User::find(3);
        $listener($this->createMock(AbstractSerializeController::class), $user, $request);

        $this->assertTrue($user->relationLoaded('banned_ips'), 'banned_ips should be set as a loaded relation');
        $this->assertArrayNotHasKey('banned_ips', $user->getAttributes(), 'banned_ips must not be stored as a model attribute');
        $this->assertFalse($user->isDirty(), 'the user instance must not be left dirty');

        // Sanity check: the relation still carries the expected data for serialization.
        $this->assertNotEmpty($user->getRelation('banned_ips'));
    }

    /**
     * Reproduces the reported incompatibility: another extension calling save() on the user
     * instance after our listener has populated its banned IPs. Previously this threw
     * "Unknown column 'banned_ips'" because the value was stored as a model attribute.
     *
     * @test
     */
    public function user_can_still_be_saved_after_show_user_listener_runs()
    {
        $request = $this->requestAsAdmin();
        $listener = $this->app()->getContainer()->make(BannedIPData::class);

        $user = User::find(3);
        $listener($this->createMock(AbstractSerializeController::class), $user, $request);

        $user->save();

        $this->assertNull(User::find(3)->getAttributes()['banned_ips'] ?? null);
    }

    /**
     * @test
     */
    public function list_users_listener_sets_banned_ips_as_a_relation_not_an_attribute()
    {
        $request = $this->requestAsAdmin();
        $listener = $this->app()->getContainer()->make(BannedIPsData::class);

        $users = User::whereIn('id', [2, 3])->get();
        $listener($this->createMock(AbstractListController::class), $users, $request);

        foreach ($users as $user) {
            $this->assertTrue($user->relationLoaded('banned_ips'), 'banned_ips should be set as a loaded relation');
            $this->assertArrayNotHasKey('banned_ips', $user->getAttributes(), 'banned_ips must not be stored as a model attribute');

            // Must not throw "Unknown column 'banned_ips'".
            $user->save();
        }
    }
}
