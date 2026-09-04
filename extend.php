<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs;

use Carbon\Carbon;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\DiscussionResource;
use Flarum\Api\Resource\PostResource;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\Gdpr\Extend\UserData;
use Flarum\Post\Post;
use Flarum\Search\Database\DatabaseSearchDriver;
use Flarum\User\User;
use FoF\BanIPs\Api\JsonApiResponse;
use FoF\BanIPs\Api\Resource\BannedIPResource;
use FoF\BanIPs\Events\IPWasBanned;
use FoF\BanIPs\Events\IPWasUnbanned;
use FoF\BanIPs\Listeners\RemoveAccessToBannedUsers;
use FoF\BanIPs\Middleware\RegisterMiddleware;
use FoF\BanIPs\Relations\UserBannedIPs;
use FoF\BanIPs\Repositories\BannedIPRepository;
use FoF\BanIPs\Search\BannedIPSearcher;
use FoF\BanIPs\Validators\BannedIPValidator;
use Illuminate\Support\Arr;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->jsDirectory(__DIR__.'/js/dist/admin')
        ->css(__DIR__.'/resources/less/admin.less'),

    (new Extend\Frontend('common'))
        ->jsDirectory(__DIR__.'/js/dist/common'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    new Extend\ApiResource(BannedIPResource::class),

    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('isBanned')
                ->get(fn (User $user) => resolve(BannedIPRepository::class)->isUserBanned($user)),

            Schema\Boolean::make('canBanIP')
                ->get(fn (User $user, Context $context) => $context->getActor()->can('banIP', $user)),

            // The banned IPs associated with a user are not a plain Eloquent
            // relation: they include both bans tied to the user directly and bans
            // on any IP address the user has posted from. Resolve them through the
            // repository, and only when the actor is allowed to see them.
            Schema\Relationship\ToMany::make('banned_ips')
                ->type('banned_ips')
                // This relation can include bans matched by post IP (not by `user_id`),
                // so the inverse `user` relation on BannedIP must not be auto-assigned.
                ->inverse('bannedUser')
                ->includable()
                ->visible(fn (User $user, Context $context) => $context->getActor()->can('fof.ban-ips.viewBannedIPList')),
        ])
        ->endpoints(fn () => [
            // Ban every IP address a user has posted from.
            Endpoint\Endpoint::make('fof-ban-ips.ban')
                ->route('POST', '/{id}/ban')
                ->authenticated()
                ->can('banIP')
                ->action(function (Context $context) {
                    /** @var User $user */
                    $user = $context->model;
                    $actor = $context->getActor();

                    $repository = resolve(BannedIPRepository::class);
                    $validator = resolve(BannedIPValidator::class);
                    $events = resolve('events');

                    $reason = Arr::get($context->body(), 'data.attributes.reason');
                    $banned = [];

                    foreach ($repository->getUserIPs($user) as $address) {
                        $bannedIP = BannedIP::build($actor->id, $user->id, $address, $reason);
                        $bannedIP->created_at = Carbon::now();

                        $validator->assertValid($bannedIP->getAttributes());

                        $bannedIP->save();

                        $events->dispatch(new IPWasBanned($actor, $bannedIP));

                        $banned[] = $bannedIP;
                    }

                    return $banned;
                })
                ->response(fn (Context $context, array $models) => JsonApiResponse::collection($context, 'banned_ips', $models)),

            // Remove every banned IP associated with a user.
            Endpoint\Endpoint::make('fof-ban-ips.unban')
                ->route('POST', '/{id}/unban')
                ->authenticated()
                ->can('banIP')
                ->action(function (Context $context) {
                    /** @var User $user */
                    $user = $context->model;
                    $actor = $context->getActor();

                    $events = resolve('events');

                    /** @phpstan-ignore-next-line */
                    $bannedIPs = $user->banned_ips;

                    if (!$bannedIPs->isEmpty()) {
                        /** @phpstan-ignore-next-line */
                        $user->banned_ips()->delete();
                    }

                    foreach ($bannedIPs as $bannedIP) {
                        /** @var BannedIP $bannedIP */
                        $events->dispatch(new IPWasUnbanned($bannedIP, $actor));
                    }

                    return $bannedIPs;
                })
                ->response(fn (Context $context, $models) => JsonApiResponse::collection($context, 'banned_ips', $models)),

            // List the banned IPs associated with a user.
            Endpoint\Endpoint::make('fof-ban-ips.banned-ips')
                ->route('GET', '/{id}/banned-ips')
                ->authenticated()
                ->can('fof.ban-ips.viewBannedIPList')
                ->action(function (Context $context) {
                    /** @var User $user */
                    $user = $context->model;

                    /** @phpstan-ignore-next-line */
                    return $user->banned_ips;
                })
                ->response(fn (Context $context, $models) => JsonApiResponse::collection($context, 'banned_ips', $models, ['creator', 'user'])),
        ])
        // Mirror the legacy behaviour of attaching a user's banned IPs when a
        // single user is (de)serialized. The full user list is intentionally
        // excluded to avoid resolving bans for every row.
        ->endpoint(Endpoint\Show::class, fn (Endpoint\Show $endpoint) => $endpoint->addDefaultInclude(['banned_ips'])->eagerLoad(['banned_ips']))
        ->endpoint(Endpoint\Index::class, fn (Endpoint\Index $endpoint) => $endpoint->eagerLoad(['banned_ips']))
        ->endpoint(Endpoint\Create::class, fn (Endpoint\Create $endpoint) => $endpoint->addDefaultInclude(['banned_ips']))
        ->endpoint(Endpoint\Update::class, fn (Endpoint\Update $endpoint) => $endpoint->addDefaultInclude(['banned_ips'])),

    (new Extend\ApiResource(PostResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('canBanIP')
                ->get(fn (Post $post, Context $context) => $context->getActor()->can('banIP', $post->user)),

            Schema\Relationship\ToOne::make('banned_ip')
                ->type('banned_ips')
                ->nullable()
                ->includable(),
        ])
        ->endpoint(Endpoint\Show::class, fn (Endpoint\Show $endpoint) => $endpoint->addDefaultInclude(['banned_ip'])->eagerLoadWhenIncluded([
            'banned_ip' => ['banned_ip'],
            'user'      => ['user.banned_ips'],
        ]))
        ->endpoint(Endpoint\Index::class, fn (Endpoint\Index $endpoint) => $endpoint->addDefaultInclude(['banned_ip'])->eagerLoadWhenIncluded([
            'banned_ip' => ['banned_ip'],
            'user'      => ['user.banned_ips'],
        ]))
        ->endpoint(Endpoint\Create::class, fn (Endpoint\Create $endpoint) => $endpoint->addDefaultInclude(['banned_ip']))
        ->endpoint(Endpoint\Update::class, fn (Endpoint\Update $endpoint) => $endpoint->addDefaultInclude(['banned_ip'])),

    (new Extend\ApiResource(DiscussionResource::class))
        ->endpoint(Endpoint\Index::class, fn (Endpoint\Index $endpoint) => $endpoint->eagerLoadWhenIncluded([
            'lastPostedUser' => ['lastPostedUser.post_ips', 'lastPostedUser.banned_ips'],
            'user'           => ['user.post_ips', 'user.banned_ips'],
        ]))
        ->endpoint(Endpoint\Show::class, fn (Endpoint\Show $endpoint) => $endpoint->eagerLoadWhenIncluded([
            'lastPostedUser' => ['lastPostedUser.post_ips', 'lastPostedUser.banned_ips'],
            'user'           => ['user.post_ips', 'user.banned_ips'],
        ])),

    (new Extend\Model(User::class))
        ->relationship('post_ips', function (User $user) {
            return $user->hasMany(Post::class, 'user_id')->whereNotNull('ip_address')->select('user_id', 'ip_address')->distinct();
        })
        ->relationship('banned_ips', function (User $user) {
            return new UserBannedIPs(
                BannedIP::query(),
                $user,
                'banned_ips.user_id',
                'id'
            );
        }),

    (new Extend\Model(Post::class))
        ->hasOne('banned_ip', BannedIP::class, 'address', 'ip_address'),

    (new Extend\SearchDriver(DatabaseSearchDriver::class))
        ->addSearcher(BannedIP::class, BannedIPSearcher::class),

    (new Extend\Middleware('forum'))
        ->add(RegisterMiddleware::class),

    (new Extend\Event())
        ->listen(IPWasBanned::class, RemoveAccessToBannedUsers::class),

    (new Extend\Policy())
        ->modelPolicy(User::class, Access\UserPolicy::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-gdpr', fn () => [
            (new UserData())
                ->addType(Data\BannedIPData::class),
        ]),
];
