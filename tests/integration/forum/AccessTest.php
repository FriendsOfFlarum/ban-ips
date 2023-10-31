<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Tests\integration\forum;

use Carbon\Carbon;
use Flarum\Extend;
use Flarum\Http\AccessToken;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use FoF\BanIPs\Tests\fixtures\IPAddressesTrait;
use FoF\BanIPs\Tests\fixtures\IPRequestTrait;

class AccessTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use IPAddressesTrait;
    use IPRequestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabase([
            'banned_ips' => $this->getBannedIPsForDB(),
            'users'      => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'ipBanned', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'ipbanned@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'discussions' => [
                ['id' => 1, 'title' => __CLASS__, 'created_at' => Carbon::createFromDate(1975, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(1975, 5, 21)->toDateTimeString(), 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1],
                ['id' => 2, 'title' => 'lightsail in title', 'created_at' => Carbon::createFromDate(1985, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(1985, 5, 21)->toDateTimeString(), 'user_id' => 2, 'comment_count' => 1],
                ['id' => 3, 'title' => 'not in title', 'created_at' => Carbon::createFromDate(1995, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(1995, 5, 21)->toDateTimeString(), 'user_id' => 2, 'comment_count' => 1],
                ['id' => 4, 'title' => 'hidden', 'created_at' => Carbon::createFromDate(2005, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(2005, 5, 21)->toDateTimeString(), 'hidden_at' => Carbon::now()->toDateTimeString(), 'user_id' => 1, 'comment_count' => 1],
                ['id' => 5, 'title' => 'ipbanned', 'created_at' => Carbon::createFromDate(2015, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(2015, 5, 21)->toDateTimeString(), 'user_id' => 3, 'comment_count' => 2],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'created_at' => Carbon::createFromDate(1975, 5, 21)->toDateTimeString(), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>foo bar</p></t>', 'ip_address' => $this->getIPv4NotBanned()[0]],
                ['id' => 2, 'discussion_id' => 2, 'created_at' => Carbon::createFromDate(1985, 5, 21)->toDateTimeString(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>not in text</p></t>', 'ip_address' => $this->getIPv4NotBanned()[1]],
                ['id' => 3, 'discussion_id' => 3, 'created_at' => Carbon::createFromDate(1995, 5, 21)->toDateTimeString(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>lightsail in text</p></t>', 'ip_address' => $this->getIPv4NotBanned()[2]],
                ['id' => 4, 'discussion_id' => 4, 'created_at' => Carbon::createFromDate(2005, 5, 21)->toDateTimeString(), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>lightsail in text</p></t>', 'ip_address' => $this->getIPv4NotBanned()[3]],
                ['id' => 5, 'discussion_id' => 5, 'created_at' => Carbon::createFromDate(2015, 5, 21)->toDateTimeString(), 'user_id' => 3, 'type' => 'comment', 'content' => '<t><p>lightsail in text</p></t>', 'ip_address' => $this->getIPv4Banned()[1]],
                ['id' => 6, 'discussion_id' => 5, 'created_at' => Carbon::createFromDate(2015, 5, 21)->toDateTimeString(), 'user_id' => 3, 'type' => 'comment', 'content' => '<t><p>lightsail in text</p></t>', 'ip_address' => $this->getIPv6Banned()[1]],
            ],
        ]);

        $this->extend(
            (new Extend\Csrf())
                ->exemptRoute('register')
                ->exemptRoute('login')
        );

        $this->extension('fof-ban-ips');
    }

    public function bannedIPsProvider(): array
    {
        return [
            [$this->getIPv4Banned()[0]],
            [$this->getIPv6Banned()[0]],
            [$this->getIPv4Banned()[1]],
            [$this->getIPv6Banned()[1]],
            [$this->getIPv4Banned()[2]],
            [$this->getIPv6Banned()[2]],
            [$this->getIPv4Banned()[3]],
            [$this->getIPv6Banned()[3]],
        ];
    }

    public function notBannedIPsProvider(): array
    {
        return [
            [$this->getIPv4NotBanned()[0]],
            [$this->getIPv6NotBanned()[0]],
            [$this->getIPv4NotBanned()[1]],
            [$this->getIPv6NotBanned()[1]],
            [$this->getIPv4NotBanned()[2]],
            [$this->getIPv6NotBanned()[2]],
            [$this->getIPv4NotBanned()[3]],
            [$this->getIPv6NotBanned()[3]],
        ];
    }

    /**
     * @dataProvider bannedIPsProvider
     */
    public function test_banned_ips_cannot_register($bannedIP)
    {
        $response = $this->send($this->enhancedRequest('POST', '/register', [
            'serverParams' => [
                'REMOTE_ADDR' => $bannedIP,
            ],
            'json' => [
                'username' => 'test',
                'password' => 'too-obscure',
                'email'    => 'test@machine.local',
            ],
        ]));

        $this->assertEquals(422, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertJson($body);
        $this->assertEquals([
            'errors' => [
                [
                    'status' => '422',
                    'code'   => 'validation_error',
                    'detail' => 'Authorization failed.',
                    'source' => ['pointer' => '/data/attributes/ip'],
                ],
            ],
        ], json_decode($body, true));
    }

    /**
     * @dataProvider notBannedIPsProvider
     */
    public function test_not_banned_ips_can_register($notBannedIP)
    {
        $response = $this->send($this->enhancedRequest('POST', '/register', [
            'serverParams' => [
                'REMOTE_ADDR' => $notBannedIP,
            ],
            'json' => [
                'username' => 'test',
                'password' => 'too-obscure',
                'email'    => 'test@machine.local',
            ],
        ]));

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @dataProvider notBannedIPsProvider
     */
    public function test_not_banned_ip_can_login_when_user_is_not_ip_banned($notBannedIP)
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $notBannedIP,
                ],
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        // The response body should contain the user ID...
        $body = (string) $response->getBody();
        $this->assertJson($body);

        $data = json_decode($body, true);
        $this->assertEquals(2, $data['userId']);

        // ...and an access token belonging to this user.
        $token = $data['token'];
        $this->assertEquals(2, AccessToken::whereToken($token)->firstOrFail()->user_id);
    }

    /**
     * @dataProvider bannedIPsProvider
     */
    public function test_banned_ip_can_login_if_user_is_not_associated_with_a_banned_ip($bannedIP)
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $bannedIP,
                ],
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        // The response body should contain the user ID...
        $body = (string) $response->getBody();
        $this->assertJson($body);

        $data = json_decode($body, true);
        $this->assertEquals(2, $data['userId']);

        // ...and an access token belonging to this user.
        $token = $data['token'];
        $this->assertEquals(2, AccessToken::whereToken($token)->firstOrFail()->user_id);
    }

    /**
     * @dataProvider notBannedIPsProvider
     */
    public function test_non_banned_ip_can_login($notBannedIP)
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $notBannedIP,
                ],
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        // The response body should contain the user ID...
        $body = (string) $response->getBody();
        $this->assertJson($body);

        $data = json_decode($body, true);
        $this->assertEquals(2, $data['userId']);

        // ...and an access token belonging to this user.
        $token = $data['token'];
        $this->assertEquals(2, AccessToken::whereToken($token)->firstOrFail()->user_id);
    }

    public function test_banned_user_cannot_login_using_non_associated_ip()
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $this->getIPv6Banned()[3],
                ],
                'json' => [
                    'identification' => 'ipBanned',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertJson($body);
        $this->assertEquals([
            'errors' => [
                [
                    'status' => '422',
                    'code'   => 'validation_error',
                    'detail' => 'Authorization failed.',
                    'source' => ['pointer' => '/data/attributes/ip'],
                ],
            ],
        ], json_decode($body, true));
    }

    public function test_banned_user_cannot_login_using_associated_ip()
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $this->getIPv6Banned()[1],
                ],
                'json' => [
                    'identification' => 'ipBanned',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertJson($body);
        $this->assertEquals([
            'errors' => [
                [
                    'status' => '422',
                    'code'   => 'validation_error',
                    'detail' => 'Authorization failed.',
                    'source' => ['pointer' => '/data/attributes/ip'],
                ],
            ],
        ], json_decode($body, true));
    }

    /**
     * @dataProvider notBannedIPsProvider
     */
    public function test_admin_can_access_restriced_path_from_not_banned_ip($notBannedIP)
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $notBannedIP,
                ],
                'json' => [
                    'identification' => 'admin',
                    'password'       => 'password',
                ],
            ])
        );

        $response = $this->send($this->enhancedRequest('GET', '/settings', [
            'serverParams' => ['REMOTE_ADDR' => $notBannedIP],
            'cookiesFrom'  => $response,
        ]));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @dataProvider bannedIPsProvider
     */
    public function test_admin_can_access_restriced_path_from_banned_ip($bannedIP)
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $bannedIP,
                ],
                'json' => [
                    'identification' => 'admin',
                    'password'       => 'password',
                ],
            ])
        );

        $response = $this->send($this->enhancedRequest('GET', '/settings', [
            'serverParams' => ['REMOTE_ADDR' => $bannedIP],
            'cookiesFrom'  => $response,
        ]));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @dataProvider bannedIPsProvider
     */
    public function test_not_banned_user_is_able_to_access_restriced_path_from_banned_ip($bannedIP)
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $bannedIP,
                ],
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $response = $this->send($this->enhancedRequest('GET', '/settings', [
            'serverParams' => ['REMOTE_ADDR' => $bannedIP],
            'cookiesFrom'  => $response,
        ]));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @dataProvider notBannedIPsProvider
     */
    public function test_not_banned_user_is_able_to_access_restriced_path_from_not_banned_ip($notBannedIP)
    {
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $notBannedIP,
                ],
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $response = $this->send($this->enhancedRequest('GET', '/settings', [
            'serverParams' => ['REMOTE_ADDR' => $notBannedIP],
            'cookiesFrom'  => $response,
        ]));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_banned_user_cannot_access_restricted_path_from_banned_ip()
    {
        // For the purposes of the test, we will login from a non-banned IP
        $response = $this->send(
            $this->enhancedRequest('POST', '/login', [
                'serverParams' => [
                    'REMOTE_ADDR' => $this->getIPv4NotBanned()[1],
                ],
                'json' => [
                    'identification' => 'ipBanned',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        // Make sure we have logged in successfully
        $this->assertEquals(200, $response->getStatusCode());

        // Now send another request, which is authenticated with a cookie, but from a banned IP
        $response = $this->send($this->enhancedRequest('GET', '/settings', [
            'serverParams'    => ['REMOTE_ADDR' => $this->getIPv4Banned()[1]],
            'cookiesFrom'     => $response,
        ]));

        // assert that we are redirected to /logout
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/logout?token=', $response->getHeaderLine('Location'));
    }
}
