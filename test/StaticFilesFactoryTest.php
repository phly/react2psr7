<?php
/**
 * @link      http://github.com/phly/react2psr7 for the canonical source repository
 * @copyright Copyright (c) 2016 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/react2psr7/blob/master/LICENSE.md New BSD License
 */

namespace React2Psr7Test;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use React2Psr7\StaticFiles;
use React2Psr7\StaticFilesFactory;

class StaticFilesFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new StaticFilesFactory();
    }

    public function testCreatesInstanceWithDefaults()
    {
        $this->container->has('config')->willReturn(false);

        $middleware = $this->factory->__invoke($this->container->reveal());
        $this->assertInstanceOf(StaticFiles::class, $middleware);
        $this->assertAttributeEquals(
            getcwd() . '/public',
            'root',
            $middleware
        );

        $this->assertAttributeInternalType(
            'array',
            'contentTypeMap',
            $middleware
        );

        $this->assertAttributeContains(
            'text/html',
            'contentTypeMap',
            $middleware
        );
    }

    public function testCanUseConfigToProvideFileRoot()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'static_files' => [
                'root' => __DIR__,
            ],
        ]);

        $middleware = $this->factory->__invoke($this->container->reveal());
        $this->assertInstanceOf(StaticFiles::class, $middleware);
        $this->assertAttributeEquals(
            __DIR__,
            'root',
            $middleware
        );
    }

    public function testCanUseConfigToProvideContentTypeMap()
    {
        $contentTypes = [
            'php' => 'application/x-php',
        ];
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'static_files' => [
                'content_types' => $contentTypes,
            ],
        ]);

        $middleware = $this->factory->__invoke($this->container->reveal());
        $this->assertInstanceOf(StaticFiles::class, $middleware);
        $this->assertAttributeEquals(
            $contentTypes,
            'contentTypeMap',
            $middleware
        );
    }
}
