<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Middleware;

use Flarum\Api\JsonApiResponse;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;

class RegisterMiddleware implements MiddlewareInterface
{
    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    /**
     * @param BannedIPRepository $bannedIPs
     */
    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
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
        $registerUri = app('flarum.forum.routes')->getPath('register');
        $loginUri = app('flarum.forum.routes')->getPath('login');
        $requestUri = $request->getUri()->getPath();

        if ($requestUri === $registerUri || $requestUri === $loginUri) {
            $ipAddress = array_get($request->getServerParams(), 'REMOTE_ADDR', '127.0.0.1');
            $bannedIP = $ipAddress != null ? $this->bannedIPs->findByIPAddress($ipAddress) : null;

            if ($bannedIP != null && $bannedIP->deleted_at == null) {
                $error = new ResponseBag('422', [
                    [
                        'status' => '422',
                        'code'   => 'validation_error',
                        'source' => [
                            'pointer' => '/data/attributes/ip',
                        ],
                        'detail' => app('translator')->trans('fof-ban-ips.error.banned_ip_message'),
                    ],
                ]);

                $document = new Document();
                $document->setErrors($error->getErrors());

                return new JsonApiResponse($document, $error->getStatus());
            }
        }

        return $handler->handle($request);
    }
}
