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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use FoF\BanIPs\Tests\fixtures\IPAddressesTrait;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;

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

    #[Test]
    public function user_with_view_permission_sees_banned_ips_relation()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/3', [
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        // The user's banned IPs are exposed as a relation, included by default.
        $linkage = Arr::get($body, 'data.relationships.banned_ips.data', []);
        $this->assertNotEmpty($linkage, 'banned_ips relationship should be populated for a privileged actor');

        $includedBans = array_filter(
            Arr::get($body, 'included', []),
            fn ($resource) => $resource['type'] === 'banned_ips'
        );
        $this->assertNotEmpty($includedBans, 'banned IPs should be present in the included resources');
    }

    #[Test]
    public function user_without_view_permission_does_not_see_banned_ips_relation()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/3', [
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        $linkage = Arr::get($body, 'data.relationships.banned_ips.data', []);
        $this->assertEmpty($linkage, 'banned_ips must not be disclosed to an actor without the view permission');

        $includedBans = array_filter(
            Arr::get($body, 'included', []),
            fn ($resource) => $resource['type'] === 'banned_ips'
        );
        $this->assertEmpty($includedBans, 'no banned IPs should leak into the included resources');
    }

    #[Test]
    public function is_banned_attribute_is_exposed()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/3', [
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(Arr::get($body, 'data.attributes.isBanned'), 'a user posting from a banned IP should report as banned');
    }
}
