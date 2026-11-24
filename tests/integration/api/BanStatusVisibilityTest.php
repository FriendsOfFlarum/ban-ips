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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;

class BanStatusVisibilityTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'poster', 'email' => 'poster@machine.local', 'password' => 'password', 'is_email_confirmed' => 1],
                ['id' => 4, 'username' => 'viewer', 'email' => 'viewer@machine.local', 'password' => 'password', 'is_email_confirmed' => 1],
                ['id' => 5, 'username' => 'banner', 'email' => 'banner@machine.local', 'password' => 'password', 'is_email_confirmed' => 1],
            ],
            'groups' => [
                ['id' => 10, 'name_singular' => 'Viewer', 'name_plural' => 'Viewers'],
                ['id' => 11, 'name_singular' => 'Banner', 'name_plural' => 'Banners'],
            ],
            'group_user' => [
                ['group_id' => 10, 'user_id' => 4],
                ['group_id' => 11, 'user_id' => 5],
            ],
            'group_permission' => [
                ['group_id' => 10, 'permission' => 'fof.ban-ips.viewBannedIPList'],
                ['group_id' => 11, 'permission' => 'fof.ban-ips.banIP'],
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'Discussion', 'slug' => 'discussion', 'created_at' => Carbon::now()->subHour(), 'last_posted_at' => Carbon::now()->subHour(), 'user_id' => 3, 'first_post_id' => 1, 'comment_count' => 1, 'last_post_number' => 1],
            ],
            'posts' => [
                ['id' => 1, 'number' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => 3, 'type' => 'comment', 'content' => '<t><p>Hi</p></t>', 'ip_address' => '198.51.100.40'],
            ],
            'banned_ips' => [
                ['id' => 1, 'creator_id' => 1, 'user_id' => null, 'address' => '198.51.100.40', 'reason' => 'Spam', 'created_at' => Carbon::now()],
            ],
        ]);
    }

    public static function reads(): array
    {
        return [
            'user'       => ['/api/users/3', []],
            'post index' => ['/api/posts', ['filter' => ['discussion' => '1'], 'include' => 'user']],
        ];
    }

    public static function actorsWithoutBanPermissions(): array
    {
        $cases = [];

        foreach (['guest' => null, 'member' => 2] as $actor => $actorId) {
            foreach (self::reads() as $read => [$path, $query]) {
                $cases["$actor, $read"] = [$actorId, $path, $query];
            }
        }

        return $cases;
    }

    public static function actorsWithBanPermissions(): array
    {
        $cases = [];

        foreach (['admin' => 1, 'may view the ban list' => 4, 'may ban' => 5] as $actor => $actorId) {
            foreach (self::reads() as $read => [$path, $query]) {
                $cases["$actor, $read"] = [$actorId, $path, $query];
            }
        }

        return $cases;
    }

    #[Test]
    #[DataProvider('actorsWithoutBanPermissions')]
    public function actor_without_ban_permissions_does_not_see_whether_a_user_is_banned(?int $actorId, string $path, array $query)
    {
        $this->database()->enableQueryLog();

        $response = $this->send($this->get($path, $actorId, $query));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertArrayNotHasKey('isBanned', $this->poster($response)['attributes']);

        $banQueries = array_filter($this->database()->getQueryLog(), fn (array $query) => str_contains($query['query'], 'banned_ips'));
        $this->assertEmpty($banQueries, 'Nothing to work out for an actor who may not see it');
    }

    #[Test]
    #[DataProvider('actorsWithBanPermissions')]
    public function actor_with_ban_permissions_sees_whether_a_user_is_banned(int $actorId, string $path, array $query)
    {
        $response = $this->send($this->get($path, $actorId, $query));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertTrue($this->poster($response)['attributes']['isBanned']);
    }

    private function get(string $path, ?int $actorId, array $query): ServerRequestInterface
    {
        return $this->request('GET', $path, $actorId ? ['authenticatedAs' => $actorId] : [])->withQueryParams($query);
    }

    private function poster($response): array
    {
        $body = json_decode((string) $response->getBody(), true);
        $resources = array_merge(Arr::wrap(Arr::isAssoc($body['data']) ? [$body['data']] : $body['data']), $body['included'] ?? []);

        foreach ($resources as $resource) {
            if ($resource['type'] === 'users' && $resource['id'] === '3') {
                return $resource;
            }
        }

        $this->fail('User 3 is not in the response');
    }
}
