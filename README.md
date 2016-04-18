# react2psr7

[![Build Status](https://secure.travis-ci.org/phly/react2psr7.svg?branch=master)](https://secure.travis-ci.org/phly/react2psr7)
[![Coverage Status](https://coveralls.io/repos/github/phly/react2psr7/badge.svg?branch=master)](https://coveralls.io/github/phly/react2psr7?branch=master)

Serve PSR-7 middleware applications from [React](http://reactphp.org).

## Installation

```console
$ composer require "react/http:^0.5@dev" phly/react2psr7
```

> ### react/http
>
> react2psr7 currently requires features from the upcoming 0.5 release of
> react/http. Since that version is not yet released, you need to specify
> it manually when installing to force Composer to allow a development
> release.

## Usage

The following demonstrates creating an HTTP server using React, and using an
[Expressive](https://zendframework.github.io/zend-expressive/) application
to handle incoming requests.

```php
<?php
use React\EventLoop\Factory;
use React\Http\Server as HttpServer;
use React\Socket\Server as Socket;
use React2Psr7\ReactRequestHandler;
use Zend\Expressive\Application;

require_once 'vendor/autoload.php';

$loop      = Factory::create();
$socket    = new Socket($loop);
$http      = new HttpServer($socket);
$container = require 'config/container.php';

$http->on('request', new ReactRequestHandler($container->get(Application::class)));

// Listen on all ports; omit second argument to restrict to localhost.
$socket->listen(1337, '0.0.0.0');
$loop->run();
```

## Serving static files

This package also provides middleware for serving static files; this can be
useful when running React as a web server, to allow serving CSS, JavaScript, and
images.

The following demonstrates using [Stratigility](https://github.com/zendframework/zend-stratigility)
to build a middleware pipeline that consumes both the static files middleware
as well as an Expressive application in order to provide a full-fledged web
server.

```php
<?php
use React\EventLoop\Factory;
use React\Http\Server as HttpServer;
use React\Socket\Server as Socket;
use React2Psr7\ReactRequestHandler;
use React2Psr7\StaticFiles;
use Zend\Expressive\Application;
use Zend\Stratigility\MiddlewarePipe;

require_once 'vendor/autoload.php';

$loop      = Factory::create();
$socket    = new Socket($loop);
$http      = new HttpServer($socket);
$container = require 'config/container.php';
$pipeline  = new MiddlewarePipe();

$pipeline->pipe(new StaticFiles());
$pipeline->pipe($container->get(Application::class));

$http->on('request', new ReactRequestHandler($pipeline));

// Listen on all ports; omit second argument to restrict to localhost.
$socket->listen(1337, '0.0.0.0');
$loop->run();
```

(Note: you could also pipe the static files middleware into your Expressive
application.)
