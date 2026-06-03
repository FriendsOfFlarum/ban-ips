<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Tests\fixtures\IPAddressesTrait;

class DeleteBannedIPControllerTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use IPAddressesTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'moderator', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'moderator@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'group_user' => [
                ['group_id' => 4, 'user_id' => 3],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'fof.ban-ips.banIP'],
                ['group_id' => 4, 'permission' => 'fof.ban-ips.viewBannedIPList'],
            ],
            'banned_ips' => $this->getBannedIPsForDB(),
        ]);
    }

    public function test_admin_can_delete_a_banned_ip()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/fof/ban-ips/1', [
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull(BannedIP::find(1));
    }

    public function test_user_with_permission_can_delete_a_banned_ip()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/fof/ban-ips/2', [
                'authenticatedAs' => 3,
            ])
        );

        $this->assertEquals(204, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNull(BannedIP::find(2));
    }

    public function test_user_without_permission_cannot_delete_a_banned_ip()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/fof/ban-ips/1', [
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertNotNull(BannedIP::find(1));
    }
}
