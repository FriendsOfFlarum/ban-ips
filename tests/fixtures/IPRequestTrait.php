<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Tests\fixtures;

use Flarum\Testing\integration\BuildsHttpRequests;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

trait IPRequestTrait
{
    use BuildsHttpRequests;

    /**
     * Build a HTTP request that can be passed through middleware.
     *
     * @param string $method
     * @param string $path
     * @param array  $options
     *
     * @return ServerRequestInterface
     */
    protected function enhancedRequest(string $method, string $path, array $options = []): ServerRequestInterface
    {
        $serverParams = $options['serverParams'] ?? [];

        $request = new ServerRequest($serverParams, [], $path, $method);

        // Do we want a JSON request body?
        if (isset($options['json'])) {
            $request = $this->requestWithJsonBody(
                $request,
                $options['json']
            );
        }

        // Authenticate as a given user
        if (isset($options['authenticatedAs'])) {
            $request = $this->requestAsUser(
                $request,
                $options['authenticatedAs']
            );
        }

        // Let's copy the cookies from a previous response
        if (isset($options['cookiesFrom'])) {
            $request = $this->requestWithCookiesFrom(
                $request,
                $options['cookiesFrom']
            );
        }

        return $request;
    }
}
