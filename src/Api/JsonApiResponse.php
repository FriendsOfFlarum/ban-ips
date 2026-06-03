<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Api;

use Flarum\Api\Context;
use Flarum\Api\Serializer;
use Psr\Http\Message\ResponseInterface;

use function Tobyz\JsonApiServer\json_api_response;

/**
 * Helper for serializing an arbitrary collection of models to a JSON:API
 * document from within a custom endpoint's `response` callback.
 *
 * Several of this extension's endpoints return a different resource type than
 * the one they are mounted on (e.g. the `check-users` endpoints live on the
 * banned IP resource but return users), or return a collection where the
 * framework would otherwise serialize a single model. This centralises that
 * serialization so each endpoint only has to describe what it returns.
 */
abstract class JsonApiResponse
{
    /**
     * @param iterable<object> $models
     * @param string[]         $includePaths dot-separated include paths, e.g. `['banned_ips.user']`
     */
    public static function collection(Context $context, string $type, iterable $models, array $includePaths = []): ResponseInterface
    {
        // Serialize against the collection that owns the returned type so that
        // its fields' visibility rules resolve correctly, regardless of which
        // resource the endpoint is mounted on.
        $context = $context->withCollection($context->api->getCollection($type));

        $serializer = new Serializer($context);
        $resource = $context->resource($type);
        $include = self::parseInclude($includePaths);

        foreach ($models as $model) {
            $serializer->addPrimary($resource, $model, $include);
        }

        [$data, $included] = $serializer->serialize();

        return json_api_response(compact('data', 'included'));
    }

    /**
     * Turn a flat list of dot-separated include paths into the nested tree the
     * serializer expects, mirroring the framework's own include parsing.
     *
     * @param string[] $paths
     */
    private static function parseInclude(array $paths): array
    {
        $tree = [];

        foreach ($paths as $path) {
            $tree = self::insertPath($tree, explode('.', $path));
        }

        return $tree;
    }

    /**
     * @param array<string, array> $tree
     * @param string[]             $keys
     *
     * @return array<string, array>
     */
    private static function insertPath(array $tree, array $keys): array
    {
        $key = array_shift($keys);

        if ($key === null) {
            return $tree;
        }

        $tree[$key] = self::insertPath($tree[$key] ?? [], $keys);

        return $tree;
    }
}
