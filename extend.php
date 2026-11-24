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
use Flarum\Api\Resource\ForumResource;
use Flarum\Api\Resource\PostResource;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Audit\Extend\Audit;
use Flarum\Extend;
use Flarum\Gdpr\Extend\UserData;
use Flarum\Mcp\Extend\Mcp;
use Flarum\Post\Post;
use Flarum\Search\Database\DatabaseSearchDriver;
use Flarum\User\Search\UserSearcher;
use Flarum\User\User;
use FoF\BanIPs\Api\JsonApiResponse;
use FoF\BanIPs\Api\Resource\BannedIPResource;
use FoF\BanIPs\Events\IPWasBanned;
use FoF\BanIPs\Events\IPWasUnbanned;
use FoF\BanIPs\Listeners\RemoveAccessToBannedUsers;
use FoF\BanIPs\Middleware\RegisterMiddleware;
use FoF\BanIPs\Repositories\BannedIPRepository;
use FoF\BanIPs\Search\BannedIPSearcher;
use FoF\BanIPs\Search\IPBannedFilter;
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
            // Queued: the whole page is answered in two queries (BannedIPRepository::queue()).
            Schema\Boolean::make('isBanned')
                ->visible(fn (User $user, Context $context) => BannedIP::isVisibleTo($context->getActor()))
                ->get(function (User $user) {
                    $bans = resolve(BannedIPRepository::class);
                    $bans->queue($user);

                    return fn () => $bans->isUserBanned($user);
                }),

            Schema\Boolean::make('canBanIP')
                ->get(fn (User $user, Context $context) => $context->getActor()->can('banIP', $user)),

            // The banned IPs associated with a user are not a plain Eloquent
            // relation: they include both bans tied to the user directly and bans
            // on any IP address the user has posted from.
            Schema\Relationship\ToMany::make('banned_ips')
                ->type('banned_ips')
                ->includable()
                ->visible(fn (User $user, Context $context) => BannedIP::isVisibleTo($context->getActor()))
                // Empty for actors who may ban but not view the list: the bans would
                // reveal which addresses the user has posted from.
                ->get(fn (User $user, Context $context) => $context->getActor()->can('fof.ban-ips.viewBannedIPList')
                    ? resolve(BannedIPRepository::class)->getUserBannedIPs($user)->get()->all()
                    : []),
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

                    $validator = resolve(BannedIPValidator::class);
                    $events = resolve('events');

                    $reason = Arr::get($context->body(), 'data.attributes.reason');
                    $banned = [];

                    // Compared in the database, with the same equality as the unique address rule.
                    $addresses = $user->posts()
                        ->whereNotNull('ip_address')
                        ->whereNotIn('ip_address', BannedIP::query()->select('address'))
                        ->distinct()
                        ->pluck('ip_address');

                    foreach ($addresses as $address) {
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

                    $repository = resolve(BannedIPRepository::class);
                    $events = resolve('events');

                    $bannedIPs = $repository->getUserBannedIPs($user)->get();

                    // One delete for every address, not one query each. The
                    // events still go out per address: to anything listening
                    // (the audit log), each is its own unban.
                    if ($bannedIPs->isNotEmpty()) {
                        BannedIP::query()->whereKey($bannedIPs->modelKeys())->delete();
                    }

                    foreach ($bannedIPs as $bannedIP) {
                        /** @var BannedIP $bannedIP */
                        // As delete() would have left it, for listeners.
                        $bannedIP->exists = false;

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

                    return resolve(BannedIPRepository::class)->getUserBannedIPs($user)->get();
                })
                ->response(fn (Context $context, $models) => JsonApiResponse::collection($context, 'banned_ips', $models, ['creator', 'user'])),
        ])
        // Mirror the legacy behaviour of attaching a user's banned IPs when a
        // single user is (de)serialized. The full user list is intentionally
        // excluded to avoid resolving bans for every row.
        ->endpoint(Endpoint\Show::class, fn (Endpoint\Show $endpoint) => $endpoint->addDefaultInclude(['banned_ips']))
        ->endpoint(Endpoint\Create::class, fn (Endpoint\Create $endpoint) => $endpoint->addDefaultInclude(['banned_ips']))
        ->endpoint(Endpoint\Update::class, fn (Endpoint\Update $endpoint) => $endpoint->addDefaultInclude(['banned_ips'])),

    (new Extend\ApiResource(PostResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('canBanIP')
                ->get(fn (Post $post, Context $context) => $context->getActor()->can('banIP', $post->user)),

            Schema\Relationship\ToOne::make('banned_ip')
                ->type('banned_ips')
                ->nullable()
                ->includable()
                // The ban's address is the post's IP, which core only shows to those who may view it.
                ->visible(fn (Post $post, Context $context) => BannedIP::isVisibleTo($context->getActor()) && $context->getActor()->can('viewIps', $post)),
        ])
        ->endpoint(Endpoint\Show::class, fn (Endpoint\Show $endpoint) => $endpoint->addDefaultInclude(['banned_ip']))
        ->endpoint(Endpoint\Index::class, fn (Endpoint\Index $endpoint) => $endpoint->addDefaultInclude(['banned_ip']))
        ->endpoint(Endpoint\Create::class, fn (Endpoint\Create $endpoint) => $endpoint->addDefaultInclude(['banned_ip']))
        ->endpoint(Endpoint\Update::class, fn (Endpoint\Update $endpoint) => $endpoint->addDefaultInclude(['banned_ip'])),

    (new Extend\Model(User::class))
        ->hasMany('banned_ips', BannedIP::class),

    (new Extend\Model(Post::class))
        ->hasOne('banned_ip', BannedIP::class, 'address', 'ip_address'),

    (new Extend\SearchDriver(DatabaseSearchDriver::class))
        ->addSearcher(BannedIP::class, BannedIPSearcher::class)
        ->addFilter(UserSearcher::class, IPBannedFilter::class),

    (new Extend\ApiResource(ForumResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('canViewIPBans')
                ->get(fn (object $model, Context $context) => BannedIP::isVisibleTo($context->getActor())),
        ]),

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

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-mcp', fn () => [
            // No allowWrites: banning goes through the user endpoints, not raw records.
            (new Mcp())
                ->describe('banned_ips', 'IP addresses banned from the forum, each with the reason, who banned it and the user it was banned for, if any. Nobody can sign up from a banned address, and a user banned for it or who posted from it is treated as banned: they cannot log in from it and are logged out. Listing them needs permission to view the ban list.')
                ->endpointTool('users', 'fof-ban-ips.ban', 'ban_user_ips', 'Ban a user\'s IP addresses', 'Ban every IP address the user has posted from, giving the reason as attributes.reason. Everyone else who posted from those addresses is banned and logged out too, unless they may ban IPs, so confirm with the person you act for first. Addresses already banned are skipped. You cannot ban yourself or anyone who may ban IPs.')
                ->endpointTool('users', 'fof-ban-ips.unban', 'unban_user_ips', 'Unban a user\'s IP addresses', 'Remove every ban that applies to the user: those made for them and those on any address they have posted from. Other users who were banned only through those bans are unbanned too.')
                ->ability('fof.ban-ips.banIP', 'Ban IP addresses'),
        ]),

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-audit', fn () => [
            (new Audit())
                ->group('fof-ban-ips')
                ->listen(IPWasBanned::class, 'fof_ban_ips.banned', fn ($e) => array_filter([
                    'ip'      => $e->bannedIP->address,
                    'reason'  => $e->bannedIP->reason,
                    'user_id' => $e->bannedIP->user_id ?: null,
                ], fn ($v) => $v !== null))
                ->listen(IPWasUnbanned::class, 'fof_ban_ips.unbanned', fn ($e) => array_filter([
                    'ip'      => $e->unbannedIP->address,
                    'user_id' => $e->unbannedIP->user_id ?: null,
                ], fn ($v) => $v !== null)),
        ]),
];
