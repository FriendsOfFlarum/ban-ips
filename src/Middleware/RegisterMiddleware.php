<?php
namespace FoF\BanIPs\Middleware;
use Flarum\Api\Handler\IlluminateValidationExceptionHandler;
use Flarum\Api\JsonApiResponse;
use Flarum\Settings\SettingsRepositoryInterface;

use Illuminate\Contracts\Validation\ValidationException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;
use Zend\Diactoros\Uri;
class RegisterMiddleware implements MiddlewareInterface
{
    protected $settings;
    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $registerUri = '/register';
        $loginUri = '/login';
        $requestUri = $request->getUri()->getPath();

        if ($requestUri === $registerUri || $requestUri === $loginUri) {
            $data = $request->getParsedBody();
            $serverParams = $request->getServerParams();
            if (isset($serverParams['HTTP_CF_CONNECTING_IP'])) {
                 $ipAddress = $serverParams['HTTP_CF_CONNECTING_IP'];
            } else {
                 $ipAddress = $serverParams['REMOTE_ADDR'];
            }
            $settings = app(SettingsRepositoryInterface::class);
            $banlist = (array) json_decode($settings->get('fof-ban-ips.ips'));

            if (in_array($ipAddress, $banlist)) {
                $error = new ResponseBag('422', [
                    [
                        'status' => '422',
                        'code' => 'validation_error',
                        'source' => [
                            'pointer' => '/data/attributes/username'
                        ],
                        'detail' => 'Your IP has been flagged as a source of spam'
                    ]
                ]);
                $document = new Document();
                $document->setErrors($error->getErrors());
                return new JsonApiResponse($document, $error->getStatus());
            }
        }
        return $handler->handle($request);
    }
}