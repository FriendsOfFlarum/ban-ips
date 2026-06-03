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

class CheckIPsControllerTest extends TestCase
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
                ['id' => 5, 'username' => 'poster', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'poster@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'group_user' => [
                ['group_id' => 4, 'user_id' => 3],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'fof.ban-ips.banIP'],
                ['group_id' => 4, 'permission' => 'fof.ban-ips.viewBannedIPList'],
            ],
            'discussions' => [
                ['id' => 1, 'title' => __CLASS__, 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 5, 'first_post_id' => 1, 'comment_count' => 1],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 5, 'type' => 'comment', 'content' => '<t><p>foo</p></t>', 'ip_address' => $this->getIPv4NotBanned()[0]],
            ],
        ]);
    }

    public function test_user_with_permission_can_check_users_by_ip()
    {
        $response = $this->send(
            $this->request('GET', '/api/fof/ban-ips/check-users', [
                'authenticatedAs' => 3,
            ])->withQueryParams(['ip' => $this->getIPv4NotBanned()[0]])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        // User 5 posted from this IP and has no ban permission, so they should be returned.
        $ids = array_map(fn ($u) => (int) $u['id'], json_decode((string) $response->getBody(), true)['data']);
        $this->assertContains(5, $ids);
    }

    public function test_user_without_permission_cannot_check_users()
    {
        $response = $this->send(
            $this->request('GET', '/api/fof/ban-ips/check-users', [
                'authenticatedAs' => 2,
            ])->withQueryParams(['ip' => $this->getIPv4NotBanned()[0]])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_invalid_ip_is_rejected()
    {
        $response = $this->send(
            $this->request('GET', '/api/fof/ban-ips/check-users', [
                'authenticatedAs' => 3,
            ])->withQueryParams(['ip' => 'not-an-ip'])
        );

        $this->assertEquals(422, $response->getStatusCode());
    }
}
