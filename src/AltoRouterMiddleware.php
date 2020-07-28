<?php

declare(strict_types=1);

namespace Gnatsnapper\Middleware;

use AltoRouter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AltoRouterMiddleware implements MiddlewareInterface
{

    private AltoRouter $router;

    public function __construct(AltoRouter $router)
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $match = $this->router->match($request->getUri()->getPath(), $request->getMethod());

        if (empty($match)) {
            return $handler->handle($request);//No match so pass on to next middleware
        }

        if(!is_callable($match['target']))
        {

            throw new \Exception("Target not callable");

        }


        return call_user_func_array($match['target'], $match['params']);
    }
}
