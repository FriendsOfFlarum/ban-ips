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
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;

class BannedIPVisibilityTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use IPAddressesTrait;

    private const BANNED_ADDRESS = '192.168.1.1';
    private const BAN_REASON = 'private moderation note';

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $password = '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim';

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'poster', 'password' => $password, 'email' => 'poster@machine.local', 'is_email_confirmed' => 1],
                ['id' => 4, 'username' => 'viewer', 'password' => $password, 'email' => 'viewer@machine.local', 'is_email_confirmed' => 1],
                ['id' => 5, 'username' => 'banner', 'password' => $password, 'email' => 'banner@machine.local', 'is_email_confirmed' => 1],
                ['id' => 6, 'username' => 'blindbanner', 'password' => $password, 'email' => 'blindbanner@machine.local', 'is_email_confirmed' => 1],
            ],
            'groups' => [
                ['id' => 10, 'name_singular' => 'Viewer', 'name_plural' => 'Viewers'],
                ['id' => 11, 'name_singular' => 'Banner', 'name_plural' => 'Banners'],
                ['id' => 12, 'name_singular' => 'Blind banner', 'name_plural' => 'Blind banners'],
            ],
            'group_user' => [
                ['group_id' => 10, 'user_id' => 4],
                ['group_id' => 11, 'user_id' => 5],
                ['group_id' => 12, 'user_id' => 6],
            ],
            'group_permission' => [
                ['group_id' => 10, 'permission' => 'fof.ban-ips.viewBannedIPList'],
                ['group_id' => 10, 'permission' => 'discussion.viewIpsPosts'],
                ['group_id' => 11, 'permission' => 'fof.ban-ips.banIP'],
                ['group_id' => 11, 'permission' => 'discussion.viewIpsPosts'],
                ['group_id' => 12, 'permission' => 'fof.ban-ips.banIP'],
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'Discussion', 'slug' => 'discussion', 'is_private' => 0, 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 3, 'first_post_id' => 1, 'comment_count' => 2, 'last_post_number' => 2],
            ],
            'posts' => [
                ['id' => 1, 'number' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => 3, 'type' => 'comment', 'content' => '<t><p>Banned poster</p></t>', 'ip_address' => self::BANNED_ADDRESS],
                ['id' => 2, 'number' => 2, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>Member reply</p></t>', 'ip_address' => '192.168.1.100'],
            ],
            'banned_ips' => [
                ['id' => 1, 'creator_id' => 1, 'user_id' => 3, 'address' => self::BANNED_ADDRESS, 'reason' => self::BAN_REASON, 'created_at' => Carbon::now()],
                ['id' => 2, 'creator_id' => 1, 'user_id' => 2, 'address' => '192.168.1.100', 'reason' => self::BAN_REASON, 'created_at' => Carbon::now()],
            ],
        ]);
    }

    public static function postEndpoints(): array
    {
        return [
            'post show'                            => ['/api/posts/1', []],
            'post index'                           => ['/api/posts', ['filter' => ['discussion' => '1']]],
            'post show, explicit include'          => ['/api/posts/1', ['include' => 'banned_ip.creator']],
            'discussion show, first post include'  => ['/api/discussions/1', ['include' => 'firstPost.banned_ip']],
            'discussion index, first post include' => ['/api/discussions', ['include' => 'firstPost.banned_ip']],
        ];
    }

    public static function unprivilegedActorsOnPostEndpoints(): array
    {
        $cases = [];

        foreach (['guest' => null, 'member' => 2, 'may ban, not view IPs' => 6] as $actor => $actorId) {
            foreach (self::postEndpoints() as $endpoint => [$path, $query]) {
                $cases["$actor, $endpoint"] = [$actorId, $path, $query];
            }
        }

        return $cases;
    }

    public static function privilegedActors(): array
    {
        return [
            'admin'                  => [1],
            'may view the ban list'  => [4],
            'may ban, not view list' => [5],
        ];
    }

    public static function banListViewers(): array
    {
        return [
            'admin'                 => [1],
            'may view the ban list' => [4],
        ];
    }

    #[Test]
    #[DataProvider('unprivilegedActorsOnPostEndpoints')]
    public function unprivileged_actor_does_not_receive_a_posts_banned_ip(?int $actorId, string $path, array $query)
    {
        $response = $this->send($this->get($path, $actorId, $query));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNoBannedIPData((string) $response->getBody());
    }

    #[Test]
    #[DataProvider('privilegedActors')]
    public function privileged_actor_receives_a_posts_banned_ip(int $actorId)
    {
        $response = $this->send($this->get('/api/posts/1', $actorId));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertEquals('1', Arr::get($body, 'data.relationships.banned_ip.data.id'));
        $this->assertEquals([self::BANNED_ADDRESS], $this->includedBannedAddresses($body));
    }

    #[Test]
    public function viewer_receives_banned_ips_on_the_post_index()
    {
        $response = $this->send($this->get('/api/posts', 4, ['filter' => ['discussion' => '1']]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        $addresses = $this->includedBannedAddresses($body);
        sort($addresses);

        $this->assertEquals([self::BANNED_ADDRESS, '192.168.1.100'], $addresses);
    }

    #[Test]
    public function author_editing_their_post_does_not_receive_its_banned_ip()
    {
        $response = $this->send(
            $this->request('PATCH', '/api/posts/2', [
                'authenticatedAs' => 2,
                'json'            => ['data' => ['type' => 'posts', 'id' => '2', 'attributes' => ['content' => 'Edited reply']]],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNoBannedIPData((string) $response->getBody(), '192.168.1.100');
    }

    #[Test]
    #[DataProvider('unprivilegedUserReads')]
    public function unprivileged_actor_does_not_receive_a_users_banned_ips(?int $actorId, array $query)
    {
        $response = $this->send($this->get('/api/users/3', $actorId, $query));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNoBannedIPData((string) $response->getBody());
    }

    public static function unprivilegedUserReads(): array
    {
        return [
            'guest'                     => [null, []],
            'member'                    => [2, []],
            'member, explicit include'  => [2, ['include' => 'banned_ips.creator']],
        ];
    }

    #[Test]
    #[DataProvider('banListViewers')]
    public function ban_list_viewer_receives_a_users_banned_ips(int $actorId)
    {
        $response = $this->send($this->get('/api/users/3', $actorId));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertEquals([['type' => 'banned_ips', 'id' => '1']], Arr::get($body, 'data.relationships.banned_ips.data'));
        $this->assertEquals([self::BANNED_ADDRESS], $this->includedBannedAddresses($body));
    }

    #[Test]
    #[DataProvider('banOnlyUserReads')]
    public function actor_who_may_only_ban_does_not_receive_a_users_banned_ips(string $path, array $query)
    {
        $response = $this->send($this->get($path, 5, $query));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);
        $user = Arr::get($body, 'data.id') ? $body['data'] : Arr::get($body, 'data.0');

        $this->assertEquals('3', $user['id']);
        $this->assertSame([], Arr::get($user, 'relationships.banned_ips.data'));
        $this->assertEquals([], $this->includedBannedAddresses($body));
    }

    public static function banOnlyUserReads(): array
    {
        return [
            'user show'   => ['/api/users/3', []],
            'check-users' => ['/api/banned_ips/check-users', ['ipAddress' => self::BANNED_ADDRESS, 'skipValidation' => true]],
        ];
    }

    #[Test]
    public function guest_does_not_receive_banned_ips_in_the_discussion_page_payload()
    {
        $response = $this->send($this->request('GET', '/d/1'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNoBannedIPData((string) $response->getBody());
    }

    #[Test]
    public function actor_who_may_only_ban_can_still_remove_a_ban()
    {
        $response = $this->send($this->request('DELETE', '/api/banned_ips/1', ['authenticatedAs' => 5]));

        $this->assertEquals(204, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNull(BannedIP::find(1));
    }

    #[Test]
    public function actor_without_permissions_cannot_find_a_ban_to_remove()
    {
        $response = $this->send($this->request('DELETE', '/api/banned_ips/1', ['authenticatedAs' => 2]));

        $this->assertEquals(404, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNotNull(BannedIP::find(1));
    }

    #[Test]
    public function viewer_who_may_not_ban_cannot_remove_a_ban()
    {
        $response = $this->send($this->request('DELETE', '/api/banned_ips/1', ['authenticatedAs' => 4]));

        $this->assertEquals(403, $response->getStatusCode(), (string) $response->getBody());
        $this->assertNotNull(BannedIP::find(1));
    }

    #[Test]
    #[DataProvider('unprivilegedCreators')]
    public function creating_a_ban_is_authorised_before_validation(?int $actorId, int $status)
    {
        foreach ([self::BANNED_ADDRESS, '198.51.100.99'] as $address) {
            $request = $this->request('POST', '/api/banned_ips', array_filter([
                'authenticatedAs' => $actorId,
                'json'            => ['data' => ['type' => 'banned_ips', 'attributes' => ['address' => $address]]],
            ]));

            $response = $this->send($actorId ? $request : $this->requestWithCsrfToken($request));

            $this->assertEquals($status, $response->getStatusCode(), $address.': '.$response->getBody());
        }
    }

    public static function unprivilegedCreators(): array
    {
        return [
            'guest'  => [null, 401],
            'member' => [2, 403],
        ];
    }

    private function get(string $path, ?int $actorId = null, array $query = []): ServerRequestInterface
    {
        return $this->request('GET', $path, $actorId ? ['authenticatedAs' => $actorId] : [])->withQueryParams($query);
    }

    private function assertNoBannedIPData(string $body, string $address = self::BANNED_ADDRESS): void
    {
        $this->assertStringNotContainsString($address, $body);
        $this->assertStringNotContainsString(self::BAN_REASON, $body);
        $this->assertStringNotContainsString('"banned_ips"', $body);
        $this->assertStringNotContainsString('"banned_ip"', $body);
    }

    private function includedBannedAddresses(array $body): array
    {
        return array_values(array_map(
            fn (array $resource) => $resource['attributes']['address'],
            array_filter(Arr::get($body, 'included', []), fn (array $resource) => $resource['type'] === 'banned_ips')
        ));
    }
}
