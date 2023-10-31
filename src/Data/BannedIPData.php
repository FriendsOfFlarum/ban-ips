<?php

namespace FoF\BanIPs\Data;

use Blomstra\Gdpr\Data\Type;
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
                $exportData[] = ["bannedIP/{$bannedIp->id}.json" => $this->sanitize($bannedIp)];
            });

        return $exportData;
    }

    public function sanitize(BannedIP $bannedIP): array
    {
        return Arr::except($bannedIP, ['id', 'creator_id', 'user_id']);
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
