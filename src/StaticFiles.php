<?php
/**
 * @link      http://github.com/phly/react2psr7 for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/react2psr7/blob/master/LICENSE.md New BSD License
 */

namespace React2Psr7;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Zend\Diactoros\Stream;

class StaticFiles
{
    /**
     * Mapping of extensions to content types.
     *
     * Only files that match the listed extensions will be whitelisted by the
     * server, and, when returned, they'll use the provided content-type.
     *
     * @var string[]
     */
    private $contentTypeMap = [
        'css'  => 'text/css',
        'woff' => 'application/x-font-woff',
        'js'   => 'application/json',
        'gif'  => 'image/gif',
        'ico'  => 'image/ico',
        'jpg'  => 'image/jpeg',
        'png'  => 'image/png',
        'txt'  => 'text/plain',
        'html' => 'text/html',
        'md'   => 'text/markdown',
    ];

    /**
     * @var string Public root of the application, containing files to serve.
     */
    private $root;

    /**
     * @param string $root Public root of the application.
     * @param null|array $contentTypeMap Specific extension/content-type map
     *     to use with this application; if none provided, defaults are used.
     */
    public function __construct($root, array $contentTypeMap = null)
    {
        $this->root = rtrim($root, '/');

        if ($contentTypeMap) {
            $this->contentTypeMap = $contentTypeMap;
        }
    }

    /**
     * Serve static files.
     *
     * If the given request matches a file on the filesystem, and the file type
     * falls within the whitelist provided in the $contentTypeMap, this
     * middleware will populate the response with its content-type and the file
     * content.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next Next middleware to execute.
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $path = $this->root . $request->getUri()->getPath();
        if (is_dir($path)) {
            $path = rtrim($path, '/') . '/index.html';
        }

        if (! preg_match('#\.(?P<type>[a-z][a-z0-9]{0,3})$#', $path, $matches)) {
            return $next($request, $response);
        }

        $type = $matches['type'];
        if (! in_array($type, array_keys($this->contentTypeMap), true)) {
            return $next($request, $response);
        }

        if (! file_exists($path)) {
            return $next($request, $response);
        }

        return $response
            ->withHeader('Content-Type', $this->contentTypeMap[$type])
            ->withBody(new Stream($path, 'r'));
    }
}
