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

class UnbanUserControllerTest extends TestCase
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
                // The fixture associates every banned IP with user_id 3, so user 3 is the banned user.
                ['id' => 3, 'username' => 'ipBanned', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'ipbanned@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
                ['id' => 4, 'username' => 'moderator', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'moderator@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'group_user' => [
                ['group_id' => 4, 'user_id' => 4],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'fof.ban-ips.banIP'],
                ['group_id' => 4, 'permission' => 'fof.ban-ips.viewBannedIPList'],
            ],
            'banned_ips' => $this->getBannedIPsForDB(),
        ]);
    }

    public function test_user_with_permission_can_unban_a_user()
    {
        $response = $this->send(
            $this->request('POST', '/api/users/3/unban', [
                'authenticatedAs' => 4,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        // Every banned IP associated with the user should have been removed.
        $this->assertEquals(0, BannedIP::where('user_id', 3)->count());
    }

    public function test_user_without_permission_cannot_unban_a_user()
    {
        $response = $this->send(
            $this->request('POST', '/api/users/3/unban', [
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertGreaterThan(0, BannedIP::where('user_id', 3)->count());
    }
}
