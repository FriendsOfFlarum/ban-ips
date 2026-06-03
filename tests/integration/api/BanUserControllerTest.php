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

class BanUserControllerTest extends TestCase
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
                ['id' => 5, 'username' => 'target', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'target@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'group_user' => [
                ['group_id' => 4, 'user_id' => 3],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'fof.ban-ips.banIP'],
                ['group_id' => 4, 'permission' => 'fof.ban-ips.viewBannedIPList'],
            ],
            'discussions' => [
                ['id' => 1, 'title' => __CLASS__, 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 5, 'first_post_id' => 1, 'comment_count' => 2],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 5, 'type' => 'comment', 'content' => '<t><p>foo</p></t>', 'ip_address' => $this->getIPv4NotBanned()[0]],
                ['id' => 2, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 5, 'type' => 'comment', 'content' => '<t><p>bar</p></t>', 'ip_address' => $this->getIPv6NotBanned()[0]],
            ],
            'banned_ips' => $this->getBannedIPsForDB(),
        ]);
    }

    public function test_user_with_permission_can_ban_a_user()
    {
        $response = $this->send(
            $this->request('POST', '/api/users/5/ban', [
                'authenticatedAs' => 3,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Spammer',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        // Both of the target's post IPs should now be banned and associated with the user.
        $banned = BannedIP::where('user_id', 5)->pluck('address')->toArray();
        $this->assertContains($this->getIPv4NotBanned()[0], $banned);
        $this->assertContains($this->getIPv6NotBanned()[0], $banned);
    }

    public function test_user_without_permission_cannot_ban_a_user()
    {
        $response = $this->send(
            $this->request('POST', '/api/users/5/ban', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Spammer',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals(0, BannedIP::where('user_id', 5)->count());
    }

    public function test_user_cannot_ban_themselves()
    {
        $response = $this->send(
            $this->request('POST', '/api/users/3/ban', [
                'authenticatedAs' => 3,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Spammer',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_user_cannot_ban_another_user_with_ban_permission()
    {
        // User 1 is the admin, who bypasses all permission checks and so is "privileged".
        $response = $this->send(
            $this->request('POST', '/api/users/1/ban', [
                'authenticatedAs' => 3,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Spammer',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }
}
