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
use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * `canBanIP` and `isBanned` are serialized for every user on a page. For a
 * viewer who can't ban IPs (most viewers), neither may work out each user's
 * own permissions to answer: that loads their groups and runs every
 * extension's permission group processor (fof/terms reads the user's accepted
 * policies there), once per user. A viewer who can ban IPs still needs each
 * user's permissions for canBanIP.
 */
class CanBanIPQueryCountTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const AUTHORS = 8;

    /** @var array<int, true> users whose permission groups were worked out */
    private static array $processed = [];

    protected function setUp(): void
    {
        parent::setUp();

        self::$processed = [];

        $this->extension('fof-ban-ips');
        $this->extend(
            (new Extend\User())->permissionGroups(function (User $user, array $groupIds) {
                self::$processed[$user->id] = true;

                return $groupIds;
            })
        );

        $users = [$this->normalUser()];
        $posts = [];

        for ($i = 1; $i <= self::AUTHORS; $i++) {
            $users[] = ['id' => $i + 2, 'username' => "author$i", 'email' => "author$i@machine.local", 'is_email_confirmed' => 1];
            $posts[] = ['id' => $i, 'discussion_id' => 1, 'number' => $i, 'created_at' => Carbon::now(), 'user_id' => $i + 2, 'type' => 'comment', 'content' => '<t><p>Post '.$i.'</p></t>'];
        }

        $this->prepareDatabase([
            User::class       => $users,
            Discussion::class => [
                ['id' => 1, 'title' => 'Discussion', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 3, 'first_post_id' => 1, 'comment_count' => self::AUTHORS],
            ],
            Post::class       => $posts,
        ]);
    }

    #[Test]
    public function a_page_of_users_does_not_work_out_each_users_permissions()
    {
        $response = $this->send(
            $this->request('GET', '/api/posts', ['authenticatedAs' => 2])
                ->withQueryParams(['filter' => ['discussion' => 1], 'include' => 'user'])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertCount(self::AUTHORS, $body['data']);

        foreach ($body['included'] as $resource) {
            if ($resource['type'] === 'users') {
                $this->assertFalse($resource['attributes']['canBanIP']);
                $this->assertFalse($resource['attributes']['isBanned']);
            }
        }

        $this->assertSame([2], array_keys(self::$processed), "Only the viewer's own permissions are worked out");
    }
}
