<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Middleware;

use Flarum\Foundation\ErrorHandling\JsonApiFormatter;
use Flarum\Foundation\ErrorHandling\Registry;
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Flarum\User\UserRepository;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RegisterMiddleware implements MiddlewareInterface
{
    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    /**
     * @var UserRepository
     */
    private $users;

    /**
     * @var RequestUtil
     */
    private $requestUtil;

    /**
     * @param BannedIPRepository $bannedIPs
     * @param UserRepository     $users
     */
    public function __construct(BannedIPRepository $bannedIPs, UserRepository $users, RequestUtil $requestUtil)
    {
        $this->bannedIPs = $bannedIPs;
        $this->users = $users;
        $this->requestUtil = $requestUtil;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $registerUri = resolve('flarum.forum.routes')->getPath('register');
        $loginUri = resolve('flarum.forum.routes')->getPath('login');
        $logoutUri = resolve('flarum.forum.routes')->getPath('logout');
        $actor = $this->requestUtil->getActor($request);
        $requestUri = $request->getUri()->getPath();
        $ipAddress = $request->getAttribute('ipAddress1', '127.0.0.1');

        $actor->accessing_ip = $ipAddress;

        if ($requestUri === $registerUri || $requestUri === $loginUri) {
            $bannedIP = $ipAddress != null ? $this->bannedIPs->findByIPAddress($ipAddress) : null;

            if ($bannedIP !== null && $bannedIP->deleted_at === null) {
                return (new JsonApiFormatter())
                    ->format(
                        resolve(Registry::class)
                            ->handle(new ValidationException([
                                'ip' => resolve('translator')->trans('fof-ban-ips.error.banned_ip_message'),
                            ])),
                        $request
                    );
            }
        }

        // if (!$actor->isGuest() && $requestUri !== $logoutUri && $this->bannedIPs->isUserBanned($actor)) {
        //     $token = $request->getAttribute('session')->token();

        //     return new RedirectResponse($logoutUri.'?token='.$token);
        // }

        return $handler->handle($request);
    }
}
