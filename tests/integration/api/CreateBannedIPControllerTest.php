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
use PHPUnit\Framework\Attributes\DataProvider;

class CreateBannedIPControllerTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use IPAddressesTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $this->prepareDatabase([
            'users'      => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'moderator', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'moderator@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
                ['id' => 4, 'username' => 'normal2', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'normal2@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'group_user' => [
                ['group_id' => 4, 'user_id' => 3],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'fof.ban-ips.banIP'],
                ['group_id' => 4, 'permission' => 'fof.ban-ips.viewBannedIPList'],
                ['group_id' => 4, 'permission' => 'discussion.viewIpsPosts'],
            ],
            'banned_ips' => $this->getBannedIPsForDB(),
        ]);
    }

    public static function adminAndModeratorUserIdProvider(): array
    {
        return [
            ['actorId' => 1, 'userId' => 2],
            ['actorId' => 1, 'userId' => null],
            ['actorId' => 3, 'userId' => 2],
            ['actorId' => 3, 'userId' => null],
        ];
    }

    public static function permittedActorProvider(): array
    {
        return [
            'admin'     => [1],
            'moderator' => [3],
        ];
    }

    public function test_user_cannot_create_ip_ban_with_no_data()
    {
        $response = $this->send(
            $this->request('POST', '/api/banned_ips', [
                'authenticatedAs' => 1,
                'json'            => [
                    'data' => [
                        'type'       => 'banned_ips',
                        'attributes' => [],
                    ],
                ],
            ])
        );

        // The address is required, so an empty payload fails validation.
        $this->assertEquals(422, $response->getStatusCode(), (string) $response->getBody());
    }

    #[DataProvider('adminAndModeratorUserIdProvider')]
    public function test_user_with_permission_can_ban_ip_not_already_banned(int $actorId, ?int $userId)
    {
        $response = $this->send(
            $this->request('POST', '/api/banned_ips', [
                'authenticatedAs' => $actorId,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'address' => $this->getIPv6NotBanned()[2],
                            'reason'  => 'Testing',
                            'userId'  => $userId,
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());

        $body = $response->getBody();

        $this->assertJson($body);

        $attrs = json_decode($body, true)['data']['attributes'];

        $this->assertEquals($this->getIPv6NotBanned()[2], $attrs['address']);
        $this->assertEquals('Testing', $attrs['reason']);
        $this->assertEquals($userId, $attrs['userId']);
        $this->assertEquals($actorId, $attrs['creatorId']);
    }

    #[DataProvider('permittedActorProvider')]
    public function test_user_with_permission_can_ban_ip_not_already_banned_without_associated_user(int $actorId)
    {
        $response = $this->send(
            $this->request('POST', '/api/banned_ips', [
                'authenticatedAs' => $actorId,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'address' => $this->getIPv4NotBanned()[0],
                            'reason'  => 'Testing',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());

        $body = $response->getBody();

        $this->assertJson($body);

        $attrs = json_decode($body, true)['data']['attributes'];

        $this->assertEquals($this->getIPv4NotBanned()[0], $attrs['address']);
        $this->assertEquals('Testing', $attrs['reason']);
        $this->assertNull($attrs['userId']);
        $this->assertEquals($actorId, $attrs['creatorId']);
    }

    public function test_user_with_permission_cannot_ban_ip_already_banned()
    {
        $response = $this->send(
            $this->request('POST', '/api/banned_ips', [
                'authenticatedAs' => 3,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'address' => $this->getIPv6Banned()[1],
                            'reason'  => 'Testing',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertJson($body);
        $this->assertEquals([
            'errors' => [
                [
                    'status' => '422',
                    'code'   => 'validation_error',
                    'detail' => 'The address has already been taken.',
                    'source' => ['pointer' => '/data/attributes/address'],
                ],
            ],
        ], json_decode($body, true));
    }

    public function test_user_without_permission_cannnot_ban_ip()
    {
        $response = $this->send(
            $this->request('POST', '/api/banned_ips', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'attributes' => [
                            'address' => $this->getIPv4NotBanned()[1],
                            'reason'  => 'Testing',
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    // ... More test cases ...
}
