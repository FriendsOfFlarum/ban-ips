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
use Flarum\User\User;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Repositories\BannedIPRepository;
use FoF\BanIPs\Tests\fixtures\IPAddressesTrait;

class BannedIPRepositoryTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use IPAddressesTrait;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $bannedIPs = $this->getBannedIPsForDB();

        $this->prepareDatabase([
            'banned_ips' => $bannedIPs,
            'users'      => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'ipBanned', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'ipbanned@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
                ['id' => 4, 'username' => 'doubleIPUser', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'double@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
                ['id' => 5, 'username' => 'noPostsUser', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'noPosts@machine.local', 'is_email_confirmed' => 1, 'last_seen_at' => Carbon::now()->subSecond()],
            ],
            'discussions' => [
                ['id' => 1, 'title' => __CLASS__, 'created_at' => Carbon::createFromDate(1975, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(1975, 5, 21)->toDateTimeString(), 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1],
                ['id' => 2, 'title' => 'lightsail in title', 'created_at' => Carbon::createFromDate(1985, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(1985, 5, 21)->toDateTimeString(), 'user_id' => 2, 'comment_count' => 1],
                ['id' => 3, 'title' => 'not in title', 'created_at' => Carbon::createFromDate(1995, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(1995, 5, 21)->toDateTimeString(), 'user_id' => 2, 'comment_count' => 1],
                ['id' => 4, 'title' => 'hidden', 'created_at' => Carbon::createFromDate(2005, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(2005, 5, 21)->toDateTimeString(), 'hidden_at' => Carbon::now()->toDateTimeString(), 'user_id' => 1, 'comment_count' => 1],
                ['id' => 5, 'title' => 'ipbanned', 'created_at' => Carbon::createFromDate(2015, 5, 21)->toDateTimeString(), 'last_posted_at' => Carbon::createFromDate(2015, 5, 21)->toDateTimeString(), 'user_id' => 3, 'comment_count' => 1],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'created_at' => Carbon::createFromDate(1975, 5, 21)->toDateTimeString(), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>foo bar</p></t>', 'ip_address' => $this->getIPv4NotBanned()[0]],
                ['id' => 2, 'discussion_id' => 2, 'created_at' => Carbon::createFromDate(1985, 5, 21)->toDateTimeString(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>not in text</p></t>', 'ip_address' => $this->getIPv4NotBanned()[1]],
                ['id' => 3, 'discussion_id' => 3, 'created_at' => Carbon::createFromDate(1995, 5, 21)->toDateTimeString(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>lightsail in text</p></t>', 'ip_address' => $this->getIPv4NotBanned()[2]],
                ['id' => 4, 'discussion_id' => 4, 'created_at' => Carbon::createFromDate(2005, 5, 21)->toDateTimeString(), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>lightsail in text</p></t>', 'ip_address' => $this->getIPv4NotBanned()[3]],
                ['id' => 5, 'discussion_id' => 5, 'created_at' => Carbon::createFromDate(2015, 5, 21)->toDateTimeString(), 'user_id' => 3, 'type' => 'comment', 'content' => '<t><p>lightsail in text</p></t>', 'ip_address' => $this->getIPv4Banned()[1]],
                ['id' => 6, 'discussion_id' => 5, 'created_at' => Carbon::createFromDate(2015, 5, 22)->toDateTimeString(), 'user_id' => 3, 'type' => 'comment', 'content' => '<t><p>lightsail in text</p></t>', 'ip_address' => $this->getIPv6Banned()[1]],
                ['id' => 7, 'discussion_id' => 5, 'created_at' => Carbon::createFromDate(2015, 5, 23)->toDateTimeString(), 'user_id' => 4, 'type' => 'comment', 'content' => '<t><p>same IPv4 twice</p></t>', 'ip_address' => $this->getIPv4NotBanned()[0]],
                ['id' => 8, 'discussion_id' => 5, 'created_at' => Carbon::createFromDate(2015, 5, 24)->toDateTimeString(), 'user_id' => 4, 'type' => 'comment', 'content' => '<t><p>same IPv4 twice</p></t>', 'ip_address' => $this->getIPv4NotBanned()[0]],
                ['id' => 9, 'discussion_id' => 5, 'created_at' => Carbon::createFromDate(2015, 5, 24)->toDateTimeString(), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>admin post from banned ip</p></t>', 'ip_address' => $this->getIPv6Banned()[3]],
            ],
        ]);

        $this->repository = new BannedIPRepository();

        $this->extension('fof-ban-ips');

        $this->app();
    }

    /**
     * @test
     */
    public function it_checks_if_user_is_banned()
    {
        $user = User::find(2);
        $isBanned = $this->repository->isUserBanned($user);
        $this->assertFalse($isBanned, 'User should not be banned');

        $bannedUser = User::find(3);
        $isBanned = $this->repository->isUserBanned($bannedUser);
        $this->assertTrue($isBanned, 'User should be banned');
    }

    /**
     * @test
     */
    public function it_retrieves_user_ips()
    {
        $user = User::find(3);
        $ips = $this->repository->getUserIPs($user);
        $this->assertContains($this->getIPv4Banned()[1], $ips, 'The IPs should contain the banned IPv4');
        $this->assertContains($this->getIPv6Banned()[1], $ips, 'The IPs should contain the banned IPv6');
    }

    /**
     * @test
     */
    public function it_identifies_banned_ips_for_user()
    {
        $user = User::find(3);
        $bannedIps = $this->repository->getUserBannedIPs($user)->pluck('address')->toArray();
        $this->assertContains($this->getIPv4Banned()[1], $bannedIps, "The user's banned IPs should contain the given IPv4");
        $this->assertContains($this->getIPv6Banned()[1], $bannedIps, "The user's banned IPs should contain the given IPv6");
    }

    /**
     * @test
     */
    public function it_finds_banned_ip_by_id()
    {
        $bannedIP = $this->repository->findOrFail(1);
        $this->assertInstanceOf(BannedIP::class, $bannedIP);
        $this->assertEquals($this->getIPv4Banned()[0], $bannedIP->address);
    }

    /**
     * @test
     */
    public function it_finds_banned_ip_by_address()
    {
        $bannedIP = $this->repository->findByIPAddress($this->getIPv4Banned()[3]);
        $this->assertInstanceOf(BannedIP::class, $bannedIP);
        $this->assertEquals($this->getIPv4Banned()[3], $bannedIP->address);

        $bannedIP = $this->repository->findByIPAddress($this->getIPv6Banned()[2]);
        $this->assertInstanceOf(BannedIP::class, $bannedIP);
        $this->assertEquals($this->getIPv6Banned()[2], $bannedIP->address);
    }

    /**
     * @test
     */
    public function it_finds_other_users_by_ips()
    {
        $ips = [$this->getIPv4Banned()[1], $this->getIPv4Banned()[0]];
        $user = User::find(2); // normal user
        $otherUsers = $this->repository->findOtherUsers($user, $ips);
        $this->assertNotEmpty($otherUsers);
        $this->assertContains(3, $otherUsers->pluck('id')->toArray()); // User ID of ipBanned

        $ips = [$this->getIPv6Banned()[1], $this->getIPv6Banned()[0]];
        $user = User::find(2); // normal user
        $otherUsers = $this->repository->findOtherUsers($user, $ips);
        $this->assertNotEmpty($otherUsers);
        $this->assertContains(3, $otherUsers->pluck('id')->toArray()); // User ID of ipBanned
    }

    /**
     * @test
     */
    public function it_finds_users_by_ips()
    {
        $ips = [$this->getIPv4Banned()[1]];
        $users = $this->repository->findUsers($ips);
        $this->assertNotEmpty($users);
        $this->assertContains(3, $users->pluck('id')->toArray()); // User ID of ipBanned

        $ips = [$this->getIPv6Banned()[1]];
        $users = $this->repository->findUsers($ips);
        $this->assertNotEmpty($users);
        $this->assertContains(3, $users->pluck('id')->toArray()); // User ID of ipBanned
    }

    /**
     * @test
     */
    public function it_gets_user_banned_ips()
    {
        $user = User::find(3); // ipBanned user
        $bannedIPs = $this->repository->getUserBannedIPs($user)->get();
        $this->assertNotEmpty($bannedIPs);
        $this->assertContains($this->getIPv4Banned()[1], $bannedIPs->pluck('address')->toArray());
        $this->assertContains($this->getIPv6Banned()[1], $bannedIPs->pluck('address')->toArray());
    }

    /**
     * @test
     */
    public function it_handles_multiple_posts_with_same_ip_correctly()
    {
        $user = User::find(4); // doubleIPUser
        $ips = $this->repository->getUserIPs($user);
        $this->assertCount(1, $ips, 'There should be only one unique IP for the user');
        $this->assertContains($this->getIPv4NotBanned()[0], $ips, 'The IPs should contain the IPv4 used twice');
    }

    /**
     * @test
     */
    public function it_handles_users_with_same_banned_ip_correctly()
    {
        $ips = [$this->getIPv4Banned()[1]];
        $users = $this->repository->findUsers($ips);
        $this->assertContains(3, $users->pluck('id')->toArray());
        // If there's another user with the same IP, check for their ID here.
    }

    /**
     * @test
     */
    public function it_does_not_flag_non_banned_user_as_banned()
    {
        $user = User::find(2); // normal user
        $isBanned = $this->repository->isUserBanned($user);
        $this->assertFalse($isBanned, 'User should not be banned');
    }

    /**
     * @test
     */
    public function it_handles_users_with_no_posts()
    {
        $userWithoutPosts = User::find(5);
        $ips = $this->repository->getUserIPs($userWithoutPosts);
        $this->assertCount(0, $ips, 'A user with no posts should have no IP records');
    }

    /**
     * @test
     */
    public function it_identifies_multiple_banned_ips_for_user()
    {
        $user = User::find(3);
        $bannedIps = $this->repository->getUserBannedIPs($user)->pluck('address')->toArray();
        $this->assertContains($this->getIPv4Banned()[1], $bannedIps, "The user's banned IPs should contain the first banned IPv4");
        $this->assertContains($this->getIPv4Banned()[2], $bannedIps, "The user's banned IPs should contain the second banned IPv4");
    }

    /**
     * @test
     */
    public function it_returns_null_for_invalid_ip_format()
    {
        $bannedIP = $this->repository->findByIPAddress('InvalidIP');
        $this->assertNull($bannedIP);
    }

    /**
     * @test
     */
    public function it_handles_large_list_of_ips_efficiently()
    {
        // Generate a large list of IPs
        $largeListOfIps = range(1, 30000); // Adjust as needed, this is just for demonstration
        $users = $this->repository->findUsers($largeListOfIps);
        $this->assertNotNull($users, 'The system should handle large IP lists without crashing');
    }

    /**
     * @test
     */
    public function it_does_not_ban_admins_based_on_ip()
    {
        $admin = User::find(1);
        $isBanned = $this->repository->isUserBanned($admin);
        $this->assertFalse($isBanned, 'Admins should not be banned based on IP');
    }

    /**
     * @test
     */
    public function it_does_ban_normal_user_based_on_ip()
    {
        $user = User::find(3);
        $isBanned = $this->repository->isUserBanned($user);
        $this->assertTrue($isBanned, 'Normal user with a banned IP should be banned');
    }
}
