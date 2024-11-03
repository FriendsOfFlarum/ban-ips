<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Data;

use Flarum\Gdpr\Data\Type;
use FoF\BanIPs\BannedIP;
use Illuminate\Support\Arr;

class BannedIPData extends Type
{
    public function export(): ?array
    {
        $exportData = [];

        BannedIP::query()
            ->where('user_id', $this->user->id)
            ->each(function (BannedIP $bannedIp) use (&$exportData) {
                $exportData[] = ["bannedIP/ip-{$bannedIp->id}.json" => $this->encodeForExport($this->sanitize($bannedIp))];
            });

        return $exportData;
    }

    public function sanitize(BannedIP $bannedIP): array
    {
        return Arr::except($bannedIP->toArray(), ['id', 'creator_id', 'user_id']);
    }

    public function anonymize(): void
    {
        BannedIP::query()
            ->where('user_id', $this->user->id)
            ->update(['user_id' => null]);
    }

    public static function deleteDescription(): string
    {
        return self::anonymizeDescription();
    }

    public function delete(): void
    {
        $this->anonymize();
    }
}
