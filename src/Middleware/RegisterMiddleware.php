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
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;

class RegisterMiddleware implements MiddlewareInterface
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $registerUri = '/register';
        $loginUri = '/login';
        $requestUri = $request->getUri()->getPath();

        if ($requestUri === $registerUri || $requestUri === $loginUri) {
            $serverParams = $request->getServerParams();
            if (isset($serverParams['HTTP_CF_CONNECTING_IP'])) {
                $ipAddress = $serverParams['HTTP_CF_CONNECTING_IP'];
            } else {
                $ipAddress = $serverParams['REMOTE_ADDR'];
            }
            $settings = app(SettingsRepositoryInterface::class);
            $banlist = (array)json_decode($settings->get('fof-ban-ips.ips'));

            if (in_array($ipAddress, $banlist)) {
                $translator = app('translator');
                $error = new ResponseBag('422', [
                    [
                        'status' => '422',
                        'code' => 'validation_error',
                        'source' => [
                            'pointer' => '/data/attributes/username'
                        ],

                        'detail' => $translator->trans('fof-ban-ips.error.banned_ip_message')
                    ]
                ]);
                $document = new Document();
                $document->setErrors($error->getErrors());
                return new JsonApiResponse($document, $error->getStatus());
            }
        }
        return $handler->handle($request);
    }
}
