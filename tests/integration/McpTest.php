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
use Flarum\Mcp\Testing\McpTestCase;
use FoF\BanIPs\BannedIP;
use PHPUnit\Framework\Attributes\Test;

class McpTest extends McpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-ban-ips');

        $this->prepareDatabase([
            'users'       => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'other', 'email' => 'other@machine.local', 'password' => 'password', 'is_email_confirmed' => 1],
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'Discussion', 'slug' => 'discussion', 'created_at' => Carbon::now()->subHour(), 'last_posted_at' => Carbon::now()->subHour(), 'user_id' => 2, 'first_post_id' => 1, 'comment_count' => 2, 'last_post_number' => 2],
            ],
            'posts' => [
                ['id' => 1, 'number' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>One</p></t>', 'ip_address' => '198.51.100.1'],
                ['id' => 2, 'number' => 2, 'discussion_id' => 1, 'created_at' => Carbon::now()->subHour(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>Two</p></t>', 'ip_address' => '198.51.100.2'],
            ],
        ]);
    }

    #[Test]
    public function the_tools_are_offered_as_writes()
    {
        $tools = array_column($this->rpcResult($this->modern('tools/list', [], $this->bearer($this->developerToken(1))))['tools'], null, 'name');

        foreach (['ban_user_ips', 'unban_user_ips'] as $name) {
            $this->assertArrayHasKey($name, $tools);
            $this->assertSame(['id'], $tools[$name]['inputSchema']['required']);
            $this->assertFalse($tools[$name]['annotations']['readOnlyHint']);
        }
    }

    #[Test]
    public function an_admin_bans_and_unbans_a_users_addresses()
    {
        $auth = $this->bearer($this->developerToken(1));

        $banned = $this->toolOutput($this->callTool('ban_user_ips', ['id' => '2', 'attributes' => ['reason' => 'Spam']], $auth));

        $this->assertEqualsCanonicalizing(['198.51.100.1', '198.51.100.2'], array_column($banned['data'], 'address'));
        $this->assertEqualsCanonicalizing(['Spam', 'Spam'], BannedIP::query()->pluck('reason')->all());

        $this->toolOutput($this->callTool('unban_user_ips', ['id' => '2'], $auth));

        $this->assertSame(0, BannedIP::query()->count());
    }

    #[Test]
    public function a_member_cannot_ban_another_member()
    {
        $this->toolOutput($this->callTool('ban_user_ips', ['id' => '2'], $this->bearer($this->developerToken(3))), expectError: true);

        $this->assertSame(0, BannedIP::query()->count());
    }

    #[Test]
    public function a_missing_user_is_an_error_not_a_crash()
    {
        $auth = $this->bearer($this->developerToken(1));

        foreach (['ban_user_ips', 'unban_user_ips'] as $tool) {
            foreach (['', '999'] as $id) {
                $this->assertSame('Not found.', $this->toolOutput($this->callTool($tool, ['id' => $id], $auth), expectError: true)['error']);
            }
        }

        $this->assertSame(0, BannedIP::query()->count());
    }

    #[Test]
    public function the_api_description_and_abilities_mention_bans()
    {
        $auth = $this->bearer($this->developerToken(1));

        $types = array_column($this->toolOutput($this->callTool('describe_api', [], $auth))['types'], null, 'type');
        $this->assertStringContainsString('banned', $types['banned_ips']['description']);
        $this->assertSame('fof-ban-ips', $types['banned_ips']['extension']);

        $overview = json_decode($this->rpcResult($this->modern('resources/read', ['uri' => 'flarum://forum'], $auth, 'flarum://forum'))['contents'][0]['text'], true);
        $this->assertContains('fof.ban-ips.banIP', array_column($overview['you']['can'], 'ability'));
    }
}
