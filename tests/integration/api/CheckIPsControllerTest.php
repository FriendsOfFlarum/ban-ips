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
                ['id' => 6, 'username' => 'poster2', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'poster2@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'group_user' => [
                ['group_id' => 4, 'user_id' => 3],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'fof.ban-ips.banIP'],
                ['group_id' => 4, 'permission' => 'fof.ban-ips.viewBannedIPList'],
            ],
            'discussions' => [
                ['id' => 1, 'slug' => 'discussion-1', 'title' => __CLASS__, 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 5, 'first_post_id' => 1, 'comment_count' => 1],
                ['id' => 2, 'slug' => 'discussion-2', 'title' => __CLASS__, 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 6, 'first_post_id' => 3, 'comment_count' => 1],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 5, 'type' => 'comment', 'content' => '<t><p>foo</p></t>', 'ip_address' => $this->getIPv4NotBanned()[0]],
                ['id' => 2, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 5, 'type' => 'comment', 'content' => '<t><p>foo2</p></t>', 'ip_address' => $this->getIPv4Banned()[0]],
                ['id' => 3, 'discussion_id' => 2, 'created_at' => Carbon::now(), 'user_id' => 6, 'type' => 'comment', 'content' => '<t><p>bar</p></t>', 'ip_address' => $this->getIPv4NotBanned()[0]],
                ['id' => 4, 'discussion_id' => 2, 'created_at' => Carbon::now(), 'user_id' => 6, 'type' => 'comment', 'content' => '<t><p>bar2</p></t>', 'ip_address' => $this->getIPv4Banned()[1]],
            ],
            'banned_ips' => [
                ['id' => 1, 'creator_id' => 1, 'address' => $this->getIPv4Banned()[0], 'reason' => 'Testing #1', 'user_id' => null, 'created_at' => Carbon::now()],
                ['id' => 2, 'creator_id' => 1, 'address' => $this->getIPv4Banned()[1], 'reason' => 'Testing #2', 'user_id' => null, 'created_at' => Carbon::now()],
            ],
        ]);
    }

    public function test_user_with_permission_can_check_users_by_ip()
    {
        $response = $this->send(
            $this->request('GET', '/api/banned_ips/check-users', [
                'authenticatedAs' => 3,
            ])->withQueryParams(['ipAddress' => $this->getIPv4NotBanned()[0]])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        // User 5 posted from this IP and has no ban permission, so they should be returned.
        $ids = array_map(fn ($u) => (int) $u['id'], json_decode((string) $response->getBody(), true)['data']);
        $this->assertContains(5, $ids);
    }

    public function test_user_without_permission_cannot_check_users()
    {
        $response = $this->send(
            $this->request('GET', '/api/banned_ips/check-users', [
                'authenticatedAs' => 2,
            ])->withQueryParams(['ipAddress' => $this->getIPv4NotBanned()[0]])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_invalid_ip_is_rejected()
    {
        $response = $this->send(
            $this->request('GET', '/api/banned_ips/check-users', [
                'authenticatedAs' => 3,
            ])->withQueryParams(['ipAddress' => 'not-an-ip'])
        );

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * ```
     * GET /api/banned_ips/check-users?ipAddress=10.9.9.9
     *     2.x    → user 5: [{"type":"banned_ips","id":"1"}]   user 6: [{"type":"banned_ips","id":"2"}]
     *     PR 57  → user 5: [{"type":"banned_ips","id":"1"}]   user 6: {"1":{"type":"banned_ips","id":"2"}}
     * ```.
     */
    public function test_check_users_returns_list_linkage_for_each_user_banned_ips(): void
    {
        $response = $this->send(
            $this->request('GET', '/api/banned_ips/check-users', [
                'authenticatedAs' => 3,
            ])->withQueryParams(['ipAddress' => $this->getIPv4NotBanned()[0]])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        foreach ($body['data'] as $user) {
            $linkage = $user['relationships']['banned_ips']['data'] ?? null;
            $this->assertIsArray($linkage, 'banned_ips linkage should be an array');
            $this->assertTrue(array_is_list($linkage), 'banned_ips linkage must be a JSON array, not an object keyed by numeric strings');
        }
    }
}
