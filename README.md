[![Build Status](https://travis-ci.org/gnatsnapper/altorouter-middleware.svg?branch=master)](https://travis-ci.org/gnatsnapper/altorouter-middleware)

# AltoRouter Middleware

This class simply extends the venerable [AltoRouter](https://github.com/dannyvankooten/AltoRouter) class to allow use as a router/dispatcher.  If a route is not found the request is passed to the next middleware.  If a route is mapped the AltoRouter will produce a response, therefore the route must be a callable returning an object implementing Psr\Http\Message\ResponseInterface.


```php
$altorouter = new AltoRouterMiddleware();

//map array of routes

$altorouter->addRoutes(
[
    [
        'GET',
        '/',
        function () {
             $r = new Response();
             $r->getBody()->write('home');
             return $r;
        }
    ],
    [
        'GET',
        '/users',
        function () {
             $r = new Response();
             $r->getBody()->write('users');
             return $r;
        }
    ]

]
);

//or map single route

$altorouter->map(
        'GET',
        '/admin',
        function () {
             $r = new Response();
             $r->getBody()->write('admin');
             return $r;
        }
    );


```

Then add this middleware to the applications middleware pipeline.
