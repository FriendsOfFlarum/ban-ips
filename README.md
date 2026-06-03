# Ban IPs by FriendsOfFlarum

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/fof/ban-ips.svg)](https://packagist.org/packages/fof/ban-ips) [![OpenCollective](https://img.shields.io/badge/opencollective-fof-blue.svg)](https://opencollective.com/fof/donate) [![Donate](https://img.shields.io/badge/donate-datitisev-important.svg)](https://datitisev.me/donate)

A [Flarum](http://flarum.org) extension that lets moderators ban IP addresses, blocking the people behind them from logging in or registering.

Unlike Flarum's built-in user suspension — which only stops a known account — banning an IP shuts the door on the address itself. This is useful against ban-evaders who keep creating new accounts, and against spammers and bots registering from the same network.

## Features

- **Ban an individual IP address** directly from the admin panel, with an optional reason.
- **Ban a user by their IP(s)** straight from a post or the user's profile. You can choose to ban only the IP of the post in question, or every IP the user has ever posted from.
- **Catch shared accounts.** Before banning, the extension shows you which _other_ accounts have posted from the same IP address(es) so you know exactly who a ban will affect.
- **Blocks login _and_ registration** from banned IPs. Banned users are also signed out the moment they make a request from a banned address.
- **Won't lock out staff.** Users (and admins) who hold the ban permission can never be IP-banned, and a banned IP never blocks a non-banned user who simply happens to share it.
- **"Banned" badge** displayed next to affected users so moderators can spot them at a glance.
- **Manage existing bans** from the admin page: review the creator, associated user, address, reason and date, edit the reason, or remove a ban.
- **GDPR aware.** When [FoF GDPR](https://github.com/FriendsOfFlarum/gdpr) is enabled, banned-IP records are included in data exports and decoupled from users on erasure (the IP itself stays banned).

## Permissions

The extension adds two permissions, configurable per group in the admin panel:

| Permission | Description |
| --- | --- |
| **Ban IP addresses** (`fof.ban-ips.banIP`) | Create, edit and remove IP bans, and ban/unban users by IP. A moderator cannot edit a ban they created themselves, nor ban another user who also holds this permission. |
| **View banned IP address list** (`fof.ban-ips.viewBannedIPList`) | View the list of banned IPs in the admin panel and the banned IPs associated with a user. |

## How it works

A "ban" is a record of an IP address. On every forum request the extension checks the visitor's address:

- Requests to **register** or **log in** from a banned IP are rejected — unless the account being logged into is not itself associated with a banned IP.
- An authenticated user making a request from a banned IP is logged out.

When you ban a user, the extension looks up every IP address they have posted from and bans each one, so a single action covers all of the user's known addresses.

## Installation

Install with Composer:

```sh
composer require fof/ban-ips:"*"
```

## Updating

```sh
composer update fof/ban-ips
```

Then clear the cache:

```sh
php flarum cache:clear
```

## Issues

- [Open an issue on GitHub](https://github.com/FriendsOfFlarum/ban-ips/issues)

## Links

[![OpenCollective](https://img.shields.io/badge/donate-friendsofflarum-44AEE5?style=for-the-badge&logo=open-collective)](https://opencollective.com/fof/donate) [![GitHub](https://img.shields.io/badge/donate-datitisev-ea4aaa?style=for-the-badge&logo=github)](https://datitisev.me/donate/github)

- [Packagist](https://packagist.org/packages/fof/ban-ips)
- [GitHub](https://github.com/FriendsOfFlarum/ban-ips)

An extension by [FriendsOfFlarum](https://github.com/FriendsOfFlarum), commissioned by [webdeveloper.com](https://webdeveloper.com).
