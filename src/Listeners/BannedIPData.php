<?php

namespace FoF\BanIPs\Listeners;

use Flarum\Api\Controller\AbstractSerializeController;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Psr\Http\Message\ServerRequestInterface;

class BannedIPData
{
    /**
     * @var BannedIPRepository
     */
    private $bannedIPs;

    public function __construct(BannedIPRepository $bannedIPs)
    {
        $this->bannedIPs = $bannedIPs;
    }

    public function __invoke(AbstractSerializeController $controller, &$data, ServerRequestInterface $request)
    {
        $canView = $request->getAttribute('actor')->can('fof.ban-ips.viewBannedIPList');

        $data['banned_ips'] = $canView ? $this->bannedIPs->getUserBannedIPs($data)->get() : [];

        return $data;
    }
}
