<?php
/**
 * @link      http://github.com/phly/react2psr7 for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/react2psr7/blob/master/LICENSE.md New BSD License
 */

namespace React2Psr7Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface as Stream;
use Psr\Http\Message\UriInterface as Uri;
use React2Psr7\StaticFiles;

class StaticFilesTest extends TestCase
{
    public function setUp()
    {
        $this->rootDir = vfsStream::setup('public');
        $this->middleware = new StaticFiles(
            vfsStream::url('public')
        );

        $this->uri = $this->prophesize(Uri::class);
        $this->uri->getPath()->willReturn('/foo/bar');

        $this->request = $this->prophesize(Request::class);
        $this->request->getUri()->willReturn($this->uri->reveal());

        $this->response = $this->prophesize(Response::class);

        $this->next = function ($request, $response) {
            return $response;
        };
    }

    public function testReturnsEarlyIfUrlDoesNotContainFileExtension()
    {
        $this->uri->getPath()->willReturn('/foo/bar');
        $this->response->withHeader(
            'Content-Type',
            Argument::type('string')
        )->shouldNotBeCalled();
        $this->response->withBody(
            Argument::type(Stream::class)
        )->shouldNotBeCalled();

        $middleware = $this->middleware;

        $this->assertSame(
            $this->response->reveal(),
            $middleware($this->request->reveal(), $this->response->reveal(), $this->next)
        );
    }

    public function testReturnsEarlyIfExtensionIsNotInWhitelist()
    {
        $this->uri->getPath()->willReturn('/foo/bar.baz');
        $this->response->withHeader(
            'Content-Type',
            Argument::type('string')
        )->shouldNotBeCalled();
        $this->response->withBody(
            Argument::type(Stream::class)
        )->shouldNotBeCalled();

        $middleware = $this->middleware;

        $this->assertSame(
            $this->response->reveal(),
            $middleware($this->request->reveal(), $this->response->reveal(), $this->next)
        );
    }

    public function testReturnsEarlyIfExtensionIsInWhitelistButFileDoesNotExist()
    {
        $this->uri->getPath()->willReturn('/foo/bar.png');
        $this->response->withHeader(
            'Content-Type',
            Argument::type('string')
        )->shouldNotBeCalled();
        $this->response->withBody(
            Argument::type(Stream::class)
        )->shouldNotBeCalled();

        $middleware = $this->middleware;

        $this->assertSame(
            $this->response->reveal(),
            $middleware($this->request->reveal(), $this->response->reveal(), $this->next)
        );
    }

    public function testReturnsResponseRepresentingFileIfExistsAndInWhitelist()
    {
        vfsStream::newFile('/foo/bar.png')
            ->at($this->rootDir)
            ->setContent('/foo/bar.png');

        $this->uri->getPath()->willReturn('/foo/bar.png');

        $this->response->withHeader(
            'Content-Type',
            'image/png'
        )->willReturn($this->response->reveal());

        $this->response->withBody(
            Argument::that(function ($stream) {
                if (! $stream instanceof Stream) {
                    return false;
                }

                return ($stream->getContents() == '/foo/bar.png');
            })
        )->willReturn($this->response->reveal());

        $middleware = $this->middleware;

        $this->assertSame(
            $this->response->reveal(),
            $middleware($this->request->reveal(), $this->response->reveal(), $this->next)
        );
    }

    public function testReturnsIndexHtmlWhenDirectoryRequestedAndFileExistsBeneathIt()
    {
        vfsStream::newFile('/foo/index.html')
            ->at($this->rootDir)
            ->setContent('/foo/index.html');

        $this->uri->getPath()->willReturn('/foo/');

        $this->response->withHeader(
            'Content-Type',
            'text/html'
        )->willReturn($this->response->reveal());

        $this->response->withBody(
            Argument::that(function ($stream) {
                if (! $stream instanceof Stream) {
                    return false;
                }

                return ($stream->getContents() == '/foo/index.html');
            })
        )->willReturn($this->response->reveal());

        $middleware = $this->middleware;

        $this->assertSame(
            $this->response->reveal(),
            $middleware($this->request->reveal(), $this->response->reveal(), $this->next)
        );
    }
}
