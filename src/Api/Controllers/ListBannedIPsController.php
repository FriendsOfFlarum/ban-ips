<?php


namespace FoF\BanIPs\Api\Controllers;


use Flarum\Api\Controller\AbstractListController;
use Flarum\User\AssertPermissionTrait;
use FoF\BanIPs\Api\Serializers\BannedIPSerializer;
use FoF\BanIPs\BannedIP;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ListBannedIPsController extends AbstractListController
{
    use AssertPermissionTrait;

    /**
     * {@inheritdoc}
     */
    public $include = ['user'];

    /**
     * {@inheritdoc}
     */
    public $optionalInclude = ['creator'];

    /**
     * {@inheritdoc}
     */
    public $serializer = BannedIPSerializer::class;

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $this->assertCan($request->getAttribute('actor'), 'fof.banips.viewBannedIPList');

        return BannedIP::all();
    }
}