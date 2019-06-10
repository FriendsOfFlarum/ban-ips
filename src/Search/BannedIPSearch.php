<?php


namespace FoF\BanIPs\Search;


use Flarum\Search\AbstractSearch;

class BannedIPSearch extends AbstractSearch
{
    /**
     * {@inheritdoc}
     */
    protected $defaultSort = ['created_at' => 'desc'];
}