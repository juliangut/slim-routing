<?php

/*
 * slim-routing (https://github.com/juliangut/slim-routing).
 * Slim framework routing.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Source;

use Jgut\Slim\Routing\Mapping\Driver\DriverInterface;
use Jgut\Slim\Routing\Mapping\RouteMetadata;
use Jgut\Slim\Routing\Tests\Stubs\SourceStub;
use PHPUnit\Framework\TestCase;

/**
 * Abstract source tests.
 */
class AbstractSourceTest extends TestCase
{
    public function testPaths()
    {
        $paths = ['/path/to/dir'];

        $source = new SourceStub($paths);

        self::assertEquals($paths, $source->getPaths());
    }

    public function testDriver()
    {
        $driver = $this->getMockBuilder(DriverInterface::class)
            ->getMock();
        /* @var DriverInterface $driver */

        $source = new SourceStub([]);
        $source->setDriver($driver);

        self::assertEquals($driver, $source->getDriver());
    }

    public function testMetadata()
    {
        $routesMetadata = [
            (new RouteMetadata())->setName('name'),
        ];

        $driver = $this->getMockBuilder(DriverInterface::class)
            ->getMock();
        $driver->expects(self::once())
            ->method('getRoutingMetadata')
            ->will(self::returnValue($routesMetadata));
        /* @var DriverInterface $driver */

        $source = new SourceStub([], $driver);

        self::assertEquals($routesMetadata, $source->getRoutingMetadata());
    }
}
