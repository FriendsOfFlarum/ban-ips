<?php

namespace FoF\BanIPs\Api\Controllers;

use Flarum\Api\Controller\AbstractDeleteController;
use FoF\BanIPs\BanIP;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteBannedIPController extends AbstractDeleteController
{
    /**
     * @param Request $request
     */
    protected function delete(Request $request)
    {
        $id = array_get($request->getQueryParams(), 'id');

        BanIP::findOrFail($id)->delete();
    }
}
