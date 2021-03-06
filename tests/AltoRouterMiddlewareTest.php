<?php

declare(strict_types=1);

namespace Gnatsnapper\Middleware\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\{ServerRequest,Response,Uri,Stream};
use Laminas\Stratigility\MiddlewarePipe;
use Gnatsnapper\Middleware\AltoRouterMiddleware;
use AltoRouter;
use stdClass,

Exception, TypeError;

use function Laminas\Stratigility\middleware;

final class AltoRouterMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        //setup AltoRouter with callable returning Response Object
        $this->altorouter = new AltoRouterMiddleware();
        $this->altorouter->map(
            'GET',
            '/',
            function () {
                 $r = new Response();
                 $r->getBody()->write('home');
                 return $r;
            }
        );
        $this->altorouter->map(
            'GET',
            '/home/page',
            function () {
                 $r = new Response();
                 $r->getBody()->write('home-page');
                 return $r;
            }
        );
        $this->altorouter->map(
            'GET',
            '/user/[i:id]',
            function ($id) {
                 $r = new Response();
                 $r->getBody()->write($id);
                 return $r;
            }
        );
        $this->altorouter->map(
            'POST',
            '/contact',
            function () {
                 $r = new Response();
                 $r->getBody()->write('contact');
                 return $r;
            }
        );
        $this->altorouter->map(
            'GET',
            '/string',
            'string'
        );

        $this->altorouter->map(
            'GET',
            '/named',
            [$this,'namedFunction']
        );


        $this->altorouter->map(
            'GET',
            '/return',
            function () {
                return new stdClass();
            }
        );

        //Setup PSR-15 Handler
        $this->handler = new MiddlewarePipe();

        //Add AltoRouterMiddleware (class under test)
        $this->handler->pipe($this->altorouter);

        //Add fallback middleware
        $this->handler->pipe(middleware(function ($request, $handler) {

            $r = new Response();
            $r->getBody()->write('Not Found');
            return $r;
        }));
    }

    public function namedFunction(): ResponseInterface
    {
         $r = new Response();
         $r->getBody()->write('namedFunction');
         return $r;
    }

    public function testSimple(): void
    {
        $request = (new ServerRequest())->withUri(new Uri('/'));
        $response = $this->handler->handle($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame((string)$response->getBody(), 'home');
    }

    public function testSegmented(): void
    {
        $request = (new ServerRequest())->withUri(new Uri('/home/page'));
        $response = $this->handler->handle($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame((string)$response->getBody(), 'home-page');
    }

    public function testWithParameter(): void
    {
        $request = (new ServerRequest())->withUri(new Uri('/user/1'));
        $response = $this->handler->handle($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame((string)$response->getBody(), '1');
    }

    public function testHttpMethod(): void
    {
        $request = (new ServerRequest())->withUri(new Uri('/contact'))->withMethod('POST');
        $response = $this->handler->handle($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame((string)$response->getBody(), 'contact');
    }


    public function testNamedFunction(): void
    {
        $request = (new ServerRequest())->withUri(new Uri('/named'));
        $response = $this->handler->handle($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame((string)$response->getBody(), 'namedFunction');
    }

    public function testNotCallable(): void
    {
        $this->expectException(Exception::class);
        $request = (new ServerRequest())->withUri(new Uri('/string'));
        $response = $this->handler->handle($request);
    }

    public function testNotReturningServerResponse(): void
    {
        $this->expectException(TypeError::class);
        $request = (new ServerRequest())->withUri(new Uri('/return'));
        $response = $this->handler->handle($request);
    }

    public function testNotFound(): void
    {
        $request = (new ServerRequest())->withUri(new Uri('/dfjhsfhidfsuih'));
        $response = $this->handler->handle($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame((string)$response->getBody(), 'Not Found');
    }
}
