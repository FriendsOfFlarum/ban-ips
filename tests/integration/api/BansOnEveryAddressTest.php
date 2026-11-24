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
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;

class BansOnEveryAddressTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const FIRST_ADDRESS = '198.51.100.10';
    private const BANNED_ADDRESSES = ['198.51.100.20', '198.51.100.30'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $this->prepareDatabase([
            'users'       => [$this->normalUser()],
            'discussions' => [
                ['id' => 1, 'title' => 'Discussion', 'slug' => 'discussion', 'created_at' => Carbon::now()->subHour(), 'last_posted_at' => Carbon::now()->subHour(), 'user_id' => 2, 'first_post_id' => 1, 'comment_count' => 3, 'last_post_number' => 3],
            ],
            'posts' => [
                ['id' => 1, 'number' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>First</p></t>', 'ip_address' => self::FIRST_ADDRESS],
                ['id' => 2, 'number' => 2, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>Second</p></t>', 'ip_address' => self::BANNED_ADDRESSES[0]],
                ['id' => 3, 'number' => 3, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>Third</p></t>', 'ip_address' => self::BANNED_ADDRESSES[1]],
            ],
            // Bans on two of the user's three addresses, neither tied to the user.
            'banned_ips' => [
                ['id' => 1, 'creator_id' => 1, 'user_id' => null, 'address' => self::BANNED_ADDRESSES[0], 'reason' => 'By address', 'created_at' => Carbon::now()],
                ['id' => 2, 'creator_id' => 1, 'user_id' => null, 'address' => self::BANNED_ADDRESSES[1], 'reason' => 'By address', 'created_at' => Carbon::now()],
            ],
        ]);
    }

    #[Test]
    public function the_user_is_banned_when_checked_alone()
    {
        $this->app();

        $user = User::findOrFail(2);
        $bans = resolve(BannedIPRepository::class);

        $this->assertTrue($bans->isUserBanned($user));
        $this->assertEqualsCanonicalizing(self::BANNED_ADDRESSES, $bans->getUserBannedIPs($user)->pluck('address')->all());
    }

    #[Test]
    public function the_users_ban_list_includes_every_ban()
    {
        $response = $this->send($this->request('GET', '/api/users/2', ['authenticatedAs' => 1]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertTrue(Arr::get($body, 'data.attributes.isBanned'));
        $this->assertEqualsCanonicalizing(['1', '2'], array_column(Arr::get($body, 'data.relationships.banned_ips.data'), 'id'));
    }

    #[Test]
    public function unbanning_the_user_removes_every_ban()
    {
        $response = $this->send($this->request('POST', '/api/users/2/unban', ['authenticatedAs' => 1, 'json' => []]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertEquals(0, BannedIP::query()->count());
    }
}
