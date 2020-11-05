<?php

/*
 * This file is part of fof/ban-ips.
 *
 * Copyright (c) 2020 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\BanIPs\Api\Controllers;

use Flarum\Api\Controller\AbstractListController;
use Flarum\Http\UrlGenerator;
use Flarum\Search\SearchCriteria;
use Flarum\User\User;
use FoF\BanIPs\Api\Serializers\BannedIPSerializer;
use FoF\BanIPs\Search\BannedIPSearcher;
use FoF\Pages\Search\Page\PageSearcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ListBannedIPsController extends AbstractListController
{
    /**
     * {@inheritdoc}
     */
    public $serializer = BannedIPSerializer::class;

    /**
     * {@inheritdoc}
     */
    public $include = ['user', 'creator'];

    /**
     * @var PageSearcher
     */
    protected $searcher;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @param BannedIPSearcher $searcher
     * @param UrlGenerator     $url
     */
    public function __construct(BannedIPSearcher $searcher, UrlGenerator $url)
    {
        $this->searcher = $searcher;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        /**
         * @var User
         */
        $actor = $request->getAttribute('actor');

        $actor->assertCan('fof.banips.viewBannedIPList');

        $query = Arr::get($this->extractFilter($request), 'q');
        $sort = $this->extractSort($request);

        $criteria = new SearchCriteria($actor, $query, $sort);
        $limit = $this->extractLimit($request);
        $offset = $this->extractOffset($request);
        $results = $this->searcher->search($criteria, $limit, $offset);

        $document->addPaginationLinks(
            $this->url->to('api')->route('fof.ban-ips.index'),
            $request->getQueryParams(),
            $offset,
            $limit,
            $results->areMoreResults() ? null : 0
        );

        return $results->getResults();

//        return BannedIP::all();
    }
}
