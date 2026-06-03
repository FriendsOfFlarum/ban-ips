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
use FoF\BanIPs\Tests\fixtures\IPAddressesTrait;

class ListBannedIPsControllerTest extends TestCase
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
                ['group_id' => 4, 'permission' => 'fof.ban-ips.viewBannedIPList'],
            ],
            'banned_ips' => $this->getBannedIPsForDB(),
        ]);
    }

    public function test_admin_can_list_banned_ips()
    {
        $response = $this->send(
            $this->request('GET', '/api/banned_ips', [
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $data = json_decode((string) $response->getBody(), true)['data'];
        $this->assertCount(count($this->getAllBanned()), $data);
    }

    /**
     * A user granted only `fof.ban-ips.viewBannedIPList` (not an admin) must be able to
     * view the list. This guards against the permission key being checked under the wrong name.
     */
    public function test_user_with_view_permission_can_list_banned_ips()
    {
        $response = $this->send(
            $this->request('GET', '/api/banned_ips', [
                'authenticatedAs' => 3,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    public function test_user_without_permission_cannot_list_banned_ips()
    {
        $response = $this->send(
            $this->request('GET', '/api/banned_ips', [
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }
}
