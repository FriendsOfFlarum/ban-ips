<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Controller\AbstractSerializeController;
use Flarum\Database\AbstractModel;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Before an API response is serialized, queues its users (listed, and in loaded
 * relationships) so the first isBanned lookup answers for all of them in one batch.
 */
class QueueUsersForBanCheck
{
    /** Relationship levels to follow, e.g. discussion.posts.user. */
    private const MAX_DEPTH = 4;

    /** @var BannedIPRepository */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    /**
     * @param mixed $data
     */
    public function __invoke(AbstractSerializeController $controller, $data, ServerRequestInterface $request): void
    {
        $users = [];
        $seen = [];
        $this->collect($data, $users, $seen, 0);

        $this->bannedIPs->queue($users);
    }

    /**
     * @param mixed            $value
     * @param User[]           $users
     * @param array<int, true> $seen
     */
    private function collect($value, array &$users, array &$seen, int $depth): void
    {
        if ($depth > self::MAX_DEPTH) {
            return;
        }

        if ($value instanceof AbstractModel) {
            if (isset($seen[spl_object_id($value)])) {
                return;
            }

            $seen[spl_object_id($value)] = true;

            if ($value instanceof User) {
                $users[] = $value;
            }

            foreach ($value->getRelations() as $related) {
                $this->collect($related, $users, $seen, $depth + 1);
            }
        } elseif (is_iterable($value)) {
            foreach ($value as $item) {
                $this->collect($item, $users, $seen, $depth);
            }
        }
    }
}
