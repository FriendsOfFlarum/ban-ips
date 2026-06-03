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

class UpdateBannedIPControllerTest extends TestCase
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
            // Ban #1 was created by the admin, ban #2 by the moderator (user 3).
            // A ban's creator may not edit their own ban.
            'banned_ips' => [
                ['id' => 1, 'creator_id' => 1, 'user_id' => 2, 'address' => $this->getIPv4Banned()[0], 'reason' => 'Original', 'created_at' => Carbon::now()],
                ['id' => 2, 'creator_id' => 3, 'user_id' => 2, 'address' => $this->getIPv4Banned()[1], 'reason' => 'Original', 'created_at' => Carbon::now()],
            ],
        ]);
    }

    public function test_user_with_permission_can_edit_the_reason()
    {
        $response = $this->send(
            $this->request('PATCH', '/api/banned_ips/1', [
                'authenticatedAs' => 3,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Updated reason',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertEquals('Updated reason', BannedIP::find(1)->reason);
    }

    public function test_admin_can_edit_the_reason()
    {
        $response = $this->send(
            $this->request('PATCH', '/api/banned_ips/2', [
                'authenticatedAs' => 1,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Updated by admin',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertEquals('Updated by admin', BannedIP::find(2)->reason);
    }

    public function test_creator_cannot_edit_their_own_ban()
    {
        // The moderator (user 3) created ban #2.
        $response = $this->send(
            $this->request('PATCH', '/api/banned_ips/2', [
                'authenticatedAs' => 3,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Updated reason',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Original', BannedIP::find(2)->reason);
    }

    public function test_user_without_permission_cannot_edit_a_ban()
    {
        $response = $this->send(
            $this->request('PATCH', '/api/banned_ips/1', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Updated reason',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_editing_a_missing_ban_returns_not_found()
    {
        $response = $this->send(
            $this->request('PATCH', '/api/banned_ips/9999', [
                'authenticatedAs' => 3,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'reason' => 'Updated reason',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(404, $response->getStatusCode());
    }
}
