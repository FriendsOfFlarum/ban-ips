<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Api\Resource;

use Carbon\Carbon;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Flarum\Foundation\ValidationException;
use Flarum\Http\Exception\RouteNotFoundException;
use Flarum\Locale\TranslatorInterface;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\Guest;
use Flarum\User\User;
use FoF\BanIPs\Api\JsonApiResponse;
use FoF\BanIPs\BannedIP;
use FoF\BanIPs\Events\IPWasBanned;
use FoF\BanIPs\Events\IPWasUnbanned;
use FoF\BanIPs\Repositories\BannedIPRepository;
use FoF\BanIPs\Validators\BannedIPValidator;
use Illuminate\Support\Arr;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends AbstractDatabaseResource<BannedIP>
 */
class BannedIPResource extends AbstractDatabaseResource
{
    public function __construct(
        protected BannedIPRepository $bannedIPs,
        protected BannedIPValidator $validator,
        protected TranslatorInterface $translator,
    ) {
    }

    public function type(): string
    {
        return 'banned_ips';
    }

    public function model(): string
    {
        return BannedIP::class;
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->can('fof.ban-ips.viewBannedIPList')
                ->defaultInclude(['creator', 'user'])
                ->paginate(),
            Endpoint\Create::make()
                ->defaultInclude(['creator', 'user']),
            Endpoint\Update::make()
                ->can('fof.ban-ips.banIP'),
            Endpoint\Delete::make()
                ->can('fof.ban-ips.banIP'),

            // Returns the (non-privileged) users who have posted from a given IP
            // address, or from any of a target user's IP addresses. Used by the
            // ban/unban modals to warn which accounts a ban would affect.
            Endpoint\Endpoint::make('check')
                ->route('GET', '/check-users')
                ->authenticated()
                ->action(fn (Context $context) => $this->checkUsers($context))
                ->response(fn (Context $context, $users) => JsonApiResponse::collection($context, 'users', $users, ['banned_ips.user'])),
            Endpoint\Endpoint::make('check.others')
                ->route('GET', '/check-users/{user}')
                ->authenticated()
                ->action(fn (Context $context) => $this->checkUsers($context))
                ->response(fn (Context $context, $users) => JsonApiResponse::collection($context, 'users', $users, ['banned_ips.user'])),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Integer::make('creatorId')
                ->property('creator_id'),

            Schema\Integer::make('userId')
                ->property('user_id')
                ->nullable()
                ->writableOnCreate(),

            Schema\Str::make('address')
                ->requiredOnCreate()
                ->writableOnCreate()
                ->rule('ip')
                ->unique('banned_ips', 'address', true),

            Schema\Str::make('reason')
                ->nullable()
                ->writable(),

            Schema\DateTime::make('createdAt')
                ->property('created_at'),

            Schema\Relationship\ToOne::make('creator')
                ->type('users')
                ->includable(),

            Schema\Relationship\ToOne::make('user')
                ->type('users')
                ->nullable()
                ->includable(),
        ];
    }

    /**
     * @param BannedIP $model
     */
    public function creating(object $model, OriginalContext $context): ?object
    {
        $actor = $context->getActor();

        /** @var User|Guest $user */
        $user = $model->user_id ? (User::find($model->user_id) ?? new Guest()) : new Guest();

        $actor->assertCan('banIP', $user);

        $model->creator_id = $actor->id;
        $model->user_id = $user->isGuest() ? null : $user->id;
        $model->created_at = Carbon::now();

        return $model;
    }

    /**
     * @param BannedIP $model
     */
    public function created(object $model, OriginalContext $context): ?object
    {
        $this->events->dispatch(new IPWasBanned($context->getActor(), $model));

        return $model;
    }

    /**
     * @param BannedIP $model
     */
    public function updating(object $model, OriginalContext $context): ?object
    {
        // A moderator may not edit a ban they created themselves.
        if ($context->getActor()->id === $model->creator_id) {
            throw new PermissionDeniedException();
        }

        return $model;
    }

    /**
     * @param BannedIP $model
     */
    public function deleting(object $model, OriginalContext $context): void
    {
        $this->events->dispatch(new IPWasUnbanned($model, $context->getActor()));
    }

    /**
     * Resolve the users who have posted from the requested IP address(es).
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function checkUsers(Context $context)
    {
        $actor = $context->getActor();
        $params = $context->request->getQueryParams();

        $actor->assertPermission($actor->hasPermission('fof.ban-ips.banIP'));

        $userId = Arr::get($params, 'user');
        $ip = Arr::get($params, 'ipAddress');

        /** @var User|null $user */
        $user = $userId != null ? User::where('id', $userId)->orWhere('username', $userId)->first() : null;

        // When a specific user is targeted, enforce the per-user policy (e.g. cannot
        // target yourself or another user who also holds the ban permission).
        if ($user !== null) {
            $actor->assertCan('banIP', $user);
        }

        if (!isset($ip) && !isset($user)) {
            throw new RouteNotFoundException();
        }

        $validate = !Arr::has($params, 'skipValidation');

        if ($ip && $validate) {
            $this->validator->assertValid(['address' => $ip]);
        }

        $ips = Arr::wrap($ip ?? $this->bannedIPs->getUserIPs($user)->toArray());

        if (empty($ips)) {
            throw new ValidationException([
                $this->translator->trans('fof-ban-ips.error.no_ips_found_message'),
            ]);
        }

        return $user != null
            ? $this->bannedIPs->findOtherUsers($user, $ips)
            : $this->bannedIPs->findUsers($ips);
    }
}
