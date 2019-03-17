<?php

namespace FoF\BanIPs\Controllers;

use Flarum\User\AssertPermissionTrait;
use Flarum\User\User;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;

class BanIPController implements RequestHandlerInterface
{
    use AssertPermissionTrait;

    protected $settings;
    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }
    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $actor = $request->getAttribute('actor');
        $postId = array_get($request->getQueryParams(), 'id');
        $post = Post::findOrFail($postId);
        $banlist = (array) json_decode($this->settings->get('fof-ban-ips.ips'), true);
        array_push($banlist, $post->ip_address);
        $this->settings->set('fof-ban-ips.ips', json_encode($banlist));
        
        return (new Response())->withStatus(204);
    }
}