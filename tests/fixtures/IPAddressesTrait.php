<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Tests\fixtures;

use Carbon\Carbon;

trait IPAddressesTrait
{
    protected static $IPv4Banned = [
        '192.168.1.1',
        '192.168.1.100',
        '10.0.0.5',
        '172.16.0.5',
    ];

    protected static $IPv4NotBanned = [
        '192.168.1.2',
        '192.168.1.101',
        '10.0.0.6',
        '172.16.0.6',
    ];

    protected static $IPv6Banned = [
        '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        'fe80:0000:0000:0000:0204:61ff:fe9d:f156',
        '2001:0db8:0000:0042:0000:8a2e:0370:7334',
        'fe80:0000:0000:0000:0204:61ff:fe9d:f157',
    ];

    protected static $IPv6NotBanned = [
        '2001:0db8:85a3:0000:0000:8a2e:0370:7335',
        'fe80:0000:0000:0000:0204:61ff:fe9d:f158',
        '2001:0db8:0000:0042:0000:8a2e:0370:7335',
        'fe80:0000:0000:0000:0204:61ff:fe9d:f159',
    ];

    public function getIPv4Banned(): array
    {
        return self::$IPv4Banned;
    }

    public function getIPv4NotBanned(): array
    {
        return self::$IPv4NotBanned;
    }

    public function getIPv6Banned(): array
    {
        return self::$IPv6Banned;
    }

    public function getIPv6NotBanned(): array
    {
        return self::$IPv6NotBanned;
    }

    public function getAllBanned(): array
    {
        return array_merge(self::$IPv4Banned, self::$IPv6Banned);
    }

    public function getAllNotBanned(): array
    {
        return array_merge(self::$IPv4NotBanned, self::$IPv6NotBanned);
    }

    public function getBannedIPsForDB(): array
    {
        $bannedIPs = $this->getAllBanned();
        $id = 1;
        $entries = [];

        foreach ($bannedIPs as $bannedIP) {
            $entries[] = [
                'id'         => $id,
                'creator_id' => 1,
                'address'    => $bannedIP,
                'reason'     => "Testing #{$id}",
                'user_id'    => 3,
                'created_at' => Carbon::now(),
            ];

            $id++;
        }

        return $entries;
    }

    public function getRandomBannedIPv4(): string
    {
        $bannedIPs = $this->getIPv4Banned();

        return $bannedIPs[array_rand($bannedIPs)];
    }

    public function getRandomNotBannedIPv4(): string
    {
        $notBannedIPs = $this->getIPv4NotBanned();

        return $notBannedIPs[array_rand($notBannedIPs)];
    }

    public function getRandomBannedIPv6(): string
    {
        $bannedIPs = $this->getIPv6Banned();

        return $bannedIPs[array_rand($bannedIPs)];
    }

    public function getRandomNotBannedIPv6(): string
    {
        $notBannedIPs = $this->getIPv6NotBanned();

        return $notBannedIPs[array_rand($notBannedIPs)];
    }

    public function getRandomBannedIP(): string
    {
        $bannedIPs = $this->getAllBanned();

        return $bannedIPs[array_rand($bannedIPs)];
    }

    public function getRandomNotBannedIP(): string
    {
        $notBannedIPs = $this->getAllNotBanned();

        return $notBannedIPs[array_rand($notBannedIPs)];
    }
}
