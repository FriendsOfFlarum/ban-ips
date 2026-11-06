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
use Flarum\Group\Group;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Illuminate\Database\ConnectionInterface;

/**
 * canBanIP and isBanned are serialized for every post and user in a response;
 * they must not cost queries per item.
 */
class BanAttributesQueryCountTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const POSTERS = 10;

    private const MODERATOR = 20;

    private const BANNED_BY_USER = 3;

    private const BANNED_BY_ADDRESS = 4;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $users = [$this->normalUser()];
        $posts = [];
        for ($n = 0; $n < self::POSTERS; $n++) {
            $id = 3 + $n;
            $users[] = ['id' => $id, 'username' => "poster$id", 'email' => "poster$id@machine.local", 'is_email_confirmed' => 1];
            $posts[] = ['id' => $n + 1, 'number' => $n + 1, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => $id, 'type' => 'comment', 'content' => '<t><p>post</p></t>', 'ip_address' => "10.0.0.$id"];
        }

        $users[] = ['id' => self::MODERATOR, 'username' => 'moderator', 'email' => 'moderator@machine.local', 'is_email_confirmed' => 1];
        // The moderator posted from the same banned address as BANNED_BY_ADDRESS.
        $posts[] = ['id' => 50, 'number' => 50, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => self::MODERATOR, 'type' => 'comment', 'content' => '<t><p>post</p></t>', 'ip_address' => '10.0.0.'.self::BANNED_BY_ADDRESS];

        $this->prepareDatabase([
            'users'       => $users,
            'group_user'  => [['user_id' => self::MODERATOR, 'group_id' => Group::MODERATOR_ID]],
            'group_permission' => [['permission' => 'fof.ban-ips.banIP', 'group_id' => Group::MODERATOR_ID]],
            'discussions' => [
                ['id' => 1, 'title' => __CLASS__, 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 3, 'first_post_id' => 1, 'comment_count' => self::POSTERS + 1, 'last_post_number' => 50],
            ],
            'posts'      => $posts,
            'banned_ips' => [
                ['id' => 1, 'creator_id' => 1, 'address' => '192.168.1.1', 'user_id' => self::BANNED_BY_USER, 'created_at' => Carbon::now()],
                ['id' => 2, 'creator_id' => 1, 'address' => '10.0.0.'.self::BANNED_BY_ADDRESS, 'user_id' => null, 'created_at' => Carbon::now()],
            ],
        ]);
    }

    /**
     * @return array{0: array<string, mixed>, 1: int}
     */
    private function get(string $path, array $query, int $actorId, string $tables): array
    {
        $queries = 0;
        $this->app()->getContainer()->make(ConnectionInterface::class)->listen(function ($q) use (&$queries, $tables) {
            if (preg_match('/\b('.$tables.')\b/', $q->sql)) {
                $queries++;
            }
        });

        $response = $this->send($this->request('GET', $path, ['authenticatedAs' => $actorId])->withQueryParams($query));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        return [json_decode((string) $response->getBody(), true), $queries];
    }

    /**
     * @test
     */
    public function members_listing_posts_do_not_check_each_authors_permissions()
    {
        // Working out a user's permissions reads group_permission. Needed: the viewer's, and
        // (for isBanned's exemption) those of the 3 authors who match a ban; not the other 8.
        [$body, $queries] = $this->get('/api/posts', ['filter' => ['discussion' => 1]], 2, 'group_permission');

        $this->assertCount(self::POSTERS + 1, $body['data']);
        $this->assertLessThanOrEqual(1 + 3, $queries, "$queries permission queries for ".count($body['data']).' posts');

        foreach ($body['data'] as $post) {
            $this->assertFalse($post['attributes']['canBanIP']);
        }
    }

    /**
     * @test
     */
    public function moderators_still_get_can_ban_ip_per_author()
    {
        [$body] = $this->get('/api/posts', ['filter' => ['discussion' => 1]], self::MODERATOR, 'group_user');

        foreach ($body['data'] as $post) {
            $isOwnPost = (int) $post['id'] === 50;
            $this->assertSame(!$isOwnPost, $post['attributes']['canBanIP'], 'post '.$post['id']);
        }
    }

    /**
     * @test
     */
    public function is_banned_is_answered_for_a_page_of_users_in_a_fixed_number_of_queries()
    {
        [$body, $queries] = $this->get('/api/users', [], 1, 'banned_ips|ip_address');

        $this->assertGreaterThanOrEqual(self::POSTERS + 2, count($body['data']));
        // The include's own eager load, two for the banned IPs listed per user, two for isBanned.
        $this->assertLessThanOrEqual(5, $queries, "$queries ban queries for ".count($body['data']).' users');
    }

    /**
     * @test
     */
    public function is_banned_is_correct_for_each_user()
    {
        [$body] = $this->get('/api/users', [], 1, 'banned_ips');

        $banned = [];
        foreach ($body['data'] as $user) {
            $banned[(int) $user['id']] = $user['attributes']['isBanned'];
        }

        $this->assertTrue($banned[self::BANNED_BY_USER], 'banned by user id');
        $this->assertTrue($banned[self::BANNED_BY_ADDRESS], 'posted from a banned address');
        $this->assertFalse($banned[5]);
        $this->assertFalse($banned[2]);
        $this->assertFalse($banned[1], 'admins are exempt');
    }

    /**
     * @test
     */
    public function each_user_lists_the_bans_on_them_and_on_addresses_they_posted_from()
    {
        [$body] = $this->get('/api/users', ['include' => 'banned_ips'], 1, 'banned_ips');

        $listed = [];
        foreach ($body['data'] as $user) {
            $listed[(int) $user['id']] = array_column($user['relationships']['banned_ips']['data'] ?? [], 'id');
        }

        $this->assertEquals(['1'], $listed[self::BANNED_BY_USER]);
        $this->assertEquals(['2'], $listed[self::BANNED_BY_ADDRESS]);
        $this->assertEquals(['2'], $listed[self::MODERATOR], 'the moderator posted from the banned address');
        $this->assertEquals([], $listed[5]);
    }

    /**
     * @test
     */
    public function moderators_who_may_ban_ips_are_not_treated_as_banned()
    {
        [$body] = $this->get('/api/users', [], 1, 'banned_ips');

        foreach ($body['data'] as $user) {
            if ((int) $user['id'] === self::MODERATOR) {
                $this->assertFalse($user['attributes']['isBanned']);

                return;
            }
        }

        $this->fail('moderator not listed');
    }
}
