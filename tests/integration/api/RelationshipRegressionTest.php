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
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;

class RelationshipRegressionTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
                ['id' => 5, 'username' => 'poster', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'poster@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
                ['id' => 6, 'username' => 'owner', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'owner@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'discussions' => [
                ['id' => 1, 'slug' => 'discussion-1', 'title' => __CLASS__, 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 5, 'first_post_id' => 1, 'comment_count' => 2],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 5, 'type' => 'comment', 'content' => '<t><p>unbanned</p></t>', 'ip_address' => '203.0.113.77'],
                ['id' => 2, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 5, 'type' => 'comment', 'content' => '<t><p>banned</p></t>', 'ip_address' => '198.51.100.9'],
                ['id' => 3, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 5, 'type' => 'comment', 'content' => '<t><p>banned-other-owner</p></t>', 'ip_address' => '198.51.100.10'],
            ],
            'banned_ips' => [
                ['id' => 1, 'creator_id' => 1, 'address' => '198.51.100.9', 'reason' => 'Testing #1', 'user_id' => null, 'created_at' => Carbon::now()],
                ['id' => 2, 'creator_id' => 1, 'address' => '198.51.100.10', 'reason' => 'Testing #2', 'user_id' => 6, 'created_at' => Carbon::now()],
                ['id' => 203, 'creator_id' => 1, 'address' => '198.51.100.203', 'reason' => 'Testing #203', 'user_id' => null, 'created_at' => Carbon::now()],
            ],
        ]);
    }

    /**
     * Prevent this regression. Post 1 does not have banned ip, yet retrieves an unrelated ban.
     * Post with no banned ip has ip_address converted to int and matched to BannedIP (203.... -> banned ip #203).
     * ```
     * GET /api/discussions/1  (guest)
     * 2.x    → post 1  banned_ip = {"data": null}
     * PR 57  → post 1  banned_ip = {"data": {"type":"banned_ips","id":"203"}}
     * ```.
     */
    #[Test]
    public function discussion_show_does_not_attach_fake_banned_ip_to_unbanned_first_post(): void
    {
        $response = $this->send($this->request('GET', '/api/discussions/1'));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        $includedFirstPosts = array_values(array_filter(
            Arr::get($body, 'included', []),
            fn (array $resource) => $resource['type'] === 'posts' && $resource['id'] === '1'
        ));

        $this->assertCount(1, $includedFirstPosts, 'Discussion show should include first post');
        $this->assertNull(
            Arr::get($includedFirstPosts[0], 'relationships.banned_ip.data'),
            'Unbanned first post must not resolve banned_ip linkage from truncated IP octets'
        );
    }

    /**
     * Test that the user relationship for a banned IP is not overwritten when the ban is not owned by a user.
     * ```
     * GET /api/users/5?include=banned_ips.user
     *    2.x    → ban 1 (userId: null)  user relationship = null
     *    PR 57  → ban 1 (userId: null)  user relationship = {"type":"users","id":"5"}
     * ```.
     */
    #[Test]
    public function included_ban_user_relation_is_not_overwritten_for_address_matched_bans(): void
    {
        $response = $this->send(
            $this->request('GET', '/api/users/5', [
                'authenticatedAs' => 1,
            ])->withQueryParams(['include' => 'banned_ips.user'])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        $includedBans = collect(Arr::get($body, 'included', []))
            ->filter(fn (array $resource) => $resource['type'] === 'banned_ips')
            ->keyBy('id')
            ->all();

        $this->assertArrayHasKey('1', $includedBans);
        $this->assertArrayHasKey('2', $includedBans);

        $this->assertNull(
            Arr::get($includedBans['1'], 'relationships.user.data'),
            'Bans with user_id = null must keep a null user relationship'
        );
        $this->assertSame(
            ['type' => 'users', 'id' => '6'],
            Arr::get($includedBans['2'], 'relationships.user.data'),
            'Bans owned by another user must keep their real owner relationship'
        );
    }
}
