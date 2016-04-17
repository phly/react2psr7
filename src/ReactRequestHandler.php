<?php
/**
 * @link      http://github.com/phly/react2psr7 for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/react2psr7/blob/master/LICENSE.md New BSD License
 */

namespace React2Psr7;

use React\Http\Request as ReactRequest;
use React\Http\Response as ReactResponse;
use Psr\Http\Message\ResponseInterface as Psr7Response;
use Zend\Diactoros\Response as DiactorosResponse;
use Zend\Diactoros\ServerRequest as DiactorosRequest;

class ReactRequestHandler
{
    /**
     * @var callable
     */
    private $middleware;

    /**
     * @param callable $middleare PSR-7 middleware.
     */
    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * React HTTP handler.
     *
     * Marshals PSR-7 request from provided request, and executes the composed
     * middleware with that and an empty PSR-7 response.
     *
     * The returned response is used to seed the React response, which is then
     * emitted.
     *
     * @param ReactRequest $request
     * @param ReactResponse $response
     */
    public function __invoke(ReactRequest $request, ReactResponse $response)
    {
        $middleware = $this->middleware;
        $this->emit(
            $middleware($this->marshalPsr7Request($request), new DiactorosResponse()),
            $response
        );
    }

    /**
     * @param ReactRequest
     * @return DiactorosRequest
     */
    private function marshalPsr7Request(ReactRequest $reactRequest)
    {
        return new DiactorosRequest(
            $_SERVER,
            $reactRequest->getFiles(),
            $reactRequest->getUrl(),
            $reactRequest->getMethod(),
            $this->createBodyStream($reactRequest),
            $reactRequest->getHeaders(),
            [],
            $reactRequest->getQuery(),
            $reactRequest->getPost(),
            $reactRequest->getHttpVersion()
        );
    }

    /**
     * @param Psr7Response
     * @param ReactResponse
     * @return void
     */
    private function emit(Psr7Response $psr7Response, ReactResponse $reactResponse)
    {
        if (! $psr7Response->hasHeader('Content-Type')) {
            $psr7Response = $psr7Response->withHeader('Content-Type', 'text/html');
        }

        $reactResponse->writeHead(
            $psr7Response->getStatusCode(),
            $psr7Response->getHeaders()
        );

        $body = $psr7Response->getBody();
        $body->rewind();

        $reactResponse->end($body->getContents());
        $body->close();
    }

    /**
     * @param ReactRequest $reactRequest
     * @return resource
     */
    private function createBodyStream(ReactRequest $reactRequest)
    {
        $body = fopen('php://temp', 'w+');
        fwrite($body, $reactRequest->getBody());
        fseek($body, 0);
        return $body;
    }
}
