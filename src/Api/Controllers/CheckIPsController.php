<?php

namespace FoF\BanIPs\Api\Controllers;

use Flarum\Api\Controller\AbstractListController;
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Http\Exception\RouteNotFoundException;
use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use FoF\BanIPs\Repositories\BannedIPRepository;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use InvalidArgumentException;

class CheckIPsController extends AbstractListController
{
    use AssertPermissionTrait;

    /**
     * @var BannedIPRepository
     */
    private $bannedIps;

    public function __construct(BannedIPRepository $bannedIps)
    {
        $this->bannedIps = $bannedIps;
    }

    /**
     * @var string
     */
    public $serializer = UserSerializer::class;

    /**
     * Get the data to be serialized and assigned to the response document.
     *
     * @param ServerRequestInterface $request
     * @param Document $document
     * @return mixed
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $this->assertCan($request->getAttribute('actor'), 'banIP');

        $userId = $request->getQueryParams()['user'];
        $user = User::where('id', $userId)->orWhere('username', $userId)->firstOrFail();
        $ip = Arr::get($request->getQueryParams(), 'ip');

        $this->assertCan($request->getAttribute('actor'), 'banIP', $user);

        if (isset($ip) && $this->bannedIps->findByIPAddress($ip) != null) throw new RouteNotFoundException();

        $ips = Arr::wrap(
            $ip ?? $user->posts()->whereNotNull('ip_address')->pluck('ip_address')->unique()
        );


        return $this->bannedIps->findOtherUsers($user, $ips);
    }
}