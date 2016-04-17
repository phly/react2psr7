<?php
/**
 * @link      http://github.com/phly/react2psr7 for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/react2psr7/blob/master/LICENSE.md New BSD License
 */

namespace React2Psr7Test;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React2Psr7\ReactRequestHandler;
use React\Http\Request as ReactRequest;
use React\Http\Response as ReactResponse;

class ReactRequestHandlerTest extends TestCase
{
    public function testInvocationInvokesComposedMiddleware()
    {
        $middleware = function ($request, $response) {
            $this->assertInstanceOf(ServerRequestInterface::class, $request);
            $this->assertInstanceOf(ResponseInterface::class, $response);

            $response->getBody()->write('Response contents');

            return $response
                ->withStatus(401)
                ->withHeader('Authorization', 'Bearer');
        };

        $reactHandler = new ReactRequestHandler($middleware);

        $reactRequest = $this->prophesize(ReactRequest::class);
        $reactRequest->getFiles()->willReturn([]);
        $reactRequest->getUrl()->willReturn('http://localhost/foo/bar');
        $reactRequest->getMethod()->willReturn('POST');
        $reactRequest->getBody()->willReturn('BODY');
        $reactRequest->getHeaders()->willReturn([]);
        $reactRequest->getQuery()->willReturn([]);
        $reactRequest->getPost()->willReturn([]);
        $reactRequest->getHttpVersion()->willReturn('1.1');

        $reactResponse = $this->prophesize(ReactResponse::class);
        $reactResponse->writeHead(
            401,
            [
                'Authorization' => [ 'Bearer' ],
                'Content-Type'  => [ 'text/html' ],
            ]
        )->shouldBeCalled();
        $reactResponse->end('Response contents')->shouldBeCalled();

        $reactHandler($reactRequest->reveal(), $reactResponse->reveal());
    }
}
