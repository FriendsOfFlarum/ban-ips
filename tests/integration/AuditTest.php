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

use Flarum\Audit\AuditLog;
use Flarum\Audit\AuditLogger;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

class AuditTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        // Lifecycle events fired outside the test transaction shouldn't create stray entries.
        AuditLogger::$testMode = true;

        $this->extension('flarum-audit', 'fof-ban-ips');

        $this->prepareDatabase([
            'audit_log' => [],
            User::class => [
                [
                    'id'       => 3,
                    'username' => 'user3',
                    'email'    => 'user3@example.com',
                ],
            ],
        ]);
    }

    #[Test]
    public function banned()
    {
        $response = $this->send($this->request('POST', '/api/banned_ips', [
            'authenticatedAs' => 1,
            'json'            => [
                'data' => [
                    'attributes' => [
                        'address' => '192.168.2.2',
                        'reason'  => 'Because',
                    ],
                ],
            ],
        ]));

        $this->assertEquals(201, $response->getStatusCode(), $response->getBody()->getContents());

        $log = AuditLog::query()->where('action', 'fof_ban_ips.banned')->first();
        $this->assertNotNull($log);
        $this->assertEquals(1, $log->actor_id);
        $this->assertEquals([
            'ip'     => '192.168.2.2',
            'reason' => 'Because',
        ], $log->payload);
        $this->assertEquals('127.0.0.1', $log->ip_address);
    }

    #[Test]
    public function banned_user()
    {
        $response = $this->send($this->request('POST', '/api/banned_ips', [
            'authenticatedAs' => 1,
            'json'            => [
                'data' => [
                    'attributes' => [
                        'userId'  => 3,
                        'address' => '192.168.2.3',
                        'reason'  => 'Because',
                    ],
                ],
            ],
        ]));

        $this->assertEquals(201, $response->getStatusCode(), $response->getBody()->getContents());

        $log = AuditLog::query()->where('action', 'fof_ban_ips.banned')->first();
        $this->assertNotNull($log);
        $this->assertEquals(1, $log->actor_id);
        $this->assertEquals([
            'ip'      => '192.168.2.3',
            'reason'  => 'Because',
            'user_id' => 3,
        ], $log->payload);
        $this->assertEquals('127.0.0.1', $log->ip_address);
    }

    // We can't test unbanned without user because the event is not dispatched
    // https://github.com/FriendsOfFlarum/ban-ips/issues/4

    #[Test]
    public function unbanned_user()
    {
        $response = $this->send($this->request('POST', '/api/banned_ips', [
            'authenticatedAs' => 1,
            'json'            => [
                'data' => [
                    'attributes' => [
                        'userId'  => 3,
                        'address' => '192.168.2.4',
                        'reason'  => 'Because',
                    ],
                ],
            ],
        ]));

        $this->assertEquals(201, $response->getStatusCode(), $response->getBody()->getContents());

        $response = $this->send($this->request('POST', '/api/users/3/unban', [
            'authenticatedAs' => 1,
        ]));

        $this->assertEquals(200, $response->getStatusCode(), $response->getBody()->getContents());

        $log = AuditLog::query()->where('action', 'fof_ban_ips.unbanned')->first();
        $this->assertNotNull($log);
        $this->assertEquals(1, $log->actor_id);
        $this->assertEquals([
            'ip'      => '192.168.2.4',
            'user_id' => 3,
        ], $log->payload);
        $this->assertEquals('127.0.0.1', $log->ip_address);
    }
}
