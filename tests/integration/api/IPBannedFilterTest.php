<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace FoF\BanIPs\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class IPBannedFilterTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $user = fn (int $id, string $name) => ['id' => $id, 'username' => $name, 'email' => "$name@machine.local", 'password' => 'password', 'is_email_confirmed' => 1];
        $post = fn (int $id, int $userId, string $ip) => ['id' => $id, 'number' => $id, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => $userId, 'type' => 'comment', 'content' => '<t><p>Hi</p></t>', 'ip_address' => $ip];

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
                $user(3, 'bannedbyuser'),
                $user(4, 'bannedbyaddress'),
                $user(5, 'moderator'),
                $user(6, 'viewer'),
                $user(7, 'searcher'),
            ],
            'groups' => [
                ['id' => 10, 'name_singular' => 'Banner', 'name_plural' => 'Banners'],
                ['id' => 11, 'name_singular' => 'Viewer', 'name_plural' => 'Viewers'],
                ['id' => 12, 'name_singular' => 'Searcher', 'name_plural' => 'Searchers'],
            ],
            'group_user' => [
                ['group_id' => 10, 'user_id' => 5],
                ['group_id' => 11, 'user_id' => 6],
                ['group_id' => 12, 'user_id' => 7],
            ],
            'group_permission' => [
                ['group_id' => 10, 'permission' => 'fof.ban-ips.banIP'],
                ['group_id' => 11, 'permission' => 'fof.ban-ips.viewBannedIPList'],
                ['group_id' => 11, 'permission' => 'searchUsers'],
                ['group_id' => 12, 'permission' => 'searchUsers'],
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'Discussion', 'slug' => 'discussion', 'created_at' => Carbon::now()->subHour(), 'last_posted_at' => Carbon::now()->subHour(), 'user_id' => 2, 'first_post_id' => 1, 'comment_count' => 5, 'last_post_number' => 5],
            ],
            'posts' => [
                $post(1, 2, '198.51.100.1'),
                $post(2, 4, '198.51.100.1'),
                $post(3, 4, '198.51.100.2'),
                // Posted from a banned address, but may ban IPs, so aren't banned.
                $post(4, 5, '198.51.100.2'),
                $post(5, 1, '198.51.100.2'),
            ],
            'banned_ips' => [
                ['id' => 1, 'creator_id' => 1, 'user_id' => 3, 'address' => '198.51.100.9', 'reason' => null, 'created_at' => Carbon::now()],
                ['id' => 2, 'creator_id' => 1, 'user_id' => null, 'address' => '198.51.100.2', 'reason' => null, 'created_at' => Carbon::now()],
            ],
        ]);
    }

    public static function actorsWhoSeeBans(): array
    {
        return [
            'admin'                 => [1],
            'may view the ban list' => [6],
        ];
    }

    #[Test]
    #[DataProvider('actorsWhoSeeBans')]
    public function it_lists_users_who_are_ip_banned(int $actorId)
    {
        $this->assertEquals(['3', '4'], $this->userIds($actorId, ['ipbanned' => true]));
    }

    #[Test]
    #[DataProvider('actorsWhoSeeBans')]
    public function it_lists_users_who_are_not_ip_banned(int $actorId)
    {
        $this->assertEquals(['1', '2', '5', '6', '7'], $this->userIds($actorId, ['-ipbanned' => true]));
    }

    #[Test]
    public function nobody_confirmed_is_banned_when_members_may_ban_ips()
    {
        $this->prepareDatabase(['group_permission' => [['group_id' => 3, 'permission' => 'fof.ban-ips.banIP']]]);

        $this->assertEquals([], $this->userIds(1, ['ipbanned' => true]));
        $this->assertEquals(['1', '2', '3', '4', '5', '6', '7'], $this->userIds(1, ['-ipbanned' => true]));
    }

    #[Test]
    public function it_is_ignored_for_actors_who_may_not_see_bans()
    {
        $this->assertEquals(['1', '2', '3', '4', '5', '6', '7'], $this->userIds(7, ['ipbanned' => true]));
    }

    #[Test]
    public function the_forum_says_who_may_filter_by_it()
    {
        foreach ([1 => true, 6 => true, 5 => true, 7 => false, 2 => false] as $actorId => $expected) {
            $response = $this->send($this->request('GET', '/api', ['authenticatedAs' => $actorId]));
            $body = json_decode((string) $response->getBody(), true);

            $this->assertSame($expected, $body['data']['attributes']['canViewIPBans'] ?? null, "actor $actorId");
        }
    }

    #[Test]
    public function its_cost_does_not_grow_with_the_number_of_banned_users()
    {
        $users = $bans = [];

        for ($id = 100; $id < 130; $id++) {
            $users[] = ['id' => $id, 'username' => "spammer$id", 'email' => "spammer$id@machine.local", 'password' => 'password', 'is_email_confirmed' => 1];
            $bans[] = ['id' => $id, 'creator_id' => 1, 'user_id' => $id, 'address' => "198.51.101.$id", 'reason' => null, 'created_at' => Carbon::now()];
        }

        $this->prepareDatabase(['users' => $users, 'banned_ips' => $bans]);

        $this->database()->enableQueryLog();

        $response = $this->send(
            $this->request('GET', '/api/users', ['authenticatedAs' => 1])
                ->withQueryParams(['filter' => ['ipbanned' => true], 'page' => ['limit' => 5]])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertSame(32, json_decode((string) $response->getBody(), true)['meta']['page']['total']);

        foreach ($this->database()->getQueryLog() as $query) {
            $this->assertLessThan(20, count($query['bindings']), 'No query binds the banned users one by one: '.$query['query']);
        }
    }

    private function userIds(int $actorId, array $filter): array
    {
        $response = $this->send(
            $this->request('GET', '/api/users', ['authenticatedAs' => $actorId])
                ->withQueryParams(['filter' => $filter, 'page' => ['limit' => 50]])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $ids = array_column(json_decode((string) $response->getBody(), true)['data'], 'id');
        sort($ids);

        return $ids;
    }
}
