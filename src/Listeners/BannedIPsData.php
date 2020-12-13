<?php

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Controller\AbstractListController;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Psr\Http\Message\ServerRequestInterface;

class BannedIPsData
{
    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    public function __invoke(AbstractListController $controller, &$data, ServerRequestInterface $request)
    {
        $canView = $request->getAttribute('actor')->can('fof.ban-ips.viewBannedIPList');

        foreach ($data as $d) {
            $d['banned_ips'] = $canView ? $this->bannedIPs->getUserBannedIPs($d)->get() : [];
        }

        return $data;
    }
}
