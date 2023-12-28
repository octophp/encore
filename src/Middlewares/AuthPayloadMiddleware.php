<?php declare(strict_types=1);

namespace Octophp\Encore\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PsrJwt\Helper\Request;

class AuthPayloadMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $helper = new Request();
        $payload = $helper->getTokenPayload($request, 'auth');
        $request = $request->withAttribute('payload', $payload);
        $response = $handler->handle($request);
        return $response;
    }
}