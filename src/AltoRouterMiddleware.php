<?php

declare(strict_types=1);

namespace Gnatsnapper\Middleware;

use AltoRouter;
use Psr\Http\Message\{ResponseFactoryInterface,ResponseInterface,ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface,RequestHandlerInterface};
use function is_array,call_user_func_array;

class AltoRouterMiddleware extends AltoRouter implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $match = $this->match($request->getUri()->getPath(), $request->getMethod());

        if (empty($match['target'])) {

            return $handler->handle($request); //No match so pass on to next middleware

        }

        if(!is_callable($match['target']))
        {

            throw new \Exception("Target not callable");

        }

        return call_user_func_array($match['target'],$match['params']);

    }


}
