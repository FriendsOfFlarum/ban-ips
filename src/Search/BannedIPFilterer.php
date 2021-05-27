<?php

namespace FoF\BanIPs\Search;

use Flarum\Filter\AbstractFilterer;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Database\Eloquent\Builder;

class BannedIPFilterer extends AbstractFilterer
{
    protected $bannedIPs;

    public function __construct(array $filters, array $filterMutators, BannedIPRepository $bannedIPs)
    {
        parent::__construct($filters, $filterMutators);

        $this->bannedIPs = $bannedIPs;
    }

    protected function getQuery(User $actor): Builder
    {
        return $this->bannedIPs->query();
    }
}
