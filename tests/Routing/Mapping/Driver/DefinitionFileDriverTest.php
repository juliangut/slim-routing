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

use Jgut\Slim\Routing\Mapping\Driver\DefinitionFileDriver;
use Jgut\Slim\Routing\Mapping\Loader\LoaderInterface;
use Jgut\Slim\Routing\Mapping\RouteMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Definition file mapping driver factory tests.
 */
class DefinitionFileDriverTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Routing definition must be an array. "integer" given
     */
    public function testInvalidRouteDefinitionType()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([10]));
        /* @var LoaderInterface $loader */

        $driver = new DefinitionFileDriver($loader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route methods can not be empty
     */
    public function testEmptyMethods()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['methods' => ''],
            ]));
        /* @var LoaderInterface $loader */

        $driver = new DefinitionFileDriver($loader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route methods must be a string or string array. "integer" given
     */
    public function testInvalidMethods()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['methods' => 10],
            ]));
        /* @var LoaderInterface $loader */

        $driver = new DefinitionFileDriver($loader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Placeholder keys must be all strings
     */
    public function testInvalidPlaceholders()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['placeholders' => ['invalid']],
            ]));
        /* @var LoaderInterface $loader */

        $driver = new DefinitionFileDriver($loader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route middleware must be a string or string array. "integer" given
     */
    public function testInvalidMiddleware()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['middleware' => 10],
            ]));
        /* @var LoaderInterface $loader */

        $driver = new DefinitionFileDriver($loader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route invokable definition missing
     */
    public function testMissingInvokable()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([
                [],
            ]));
        /* @var LoaderInterface $loader */

        $driver = new DefinitionFileDriver($loader);

        $driver->getRoutingMetadata([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route invokable does not seam to be supported by Slim router
     */
    public function testInvalidInvokable()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['invokable' => 10],
            ]));
        /* @var LoaderInterface $loader */

        $driver = new DefinitionFileDriver($loader);

        $driver->getRoutingMetadata([]);
    }

    public function testRoutes()
    {
        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        $loader->expects(self::any())
            ->method('getMappingData')
            ->will($this->returnValue([
                [
                    'prefix' => 'abstract',
                    'pattern' => '/abstract',
                    'middleware' => ['abstractMiddleware'],
                    'routes' => [
                        [
                            'prefix' => 'grouped',
                            'pattern' => '/dependent',
                            'middleware' => ['dependentMiddleware'],
                            'routes' => [
                                [
                                    'name' => 'four',
                                    'methods' => ['GET'],
                                    'pattern' => '/four',
                                    'middleware' => ['fourMiddleware'],
                                    'invokable' => ['FourRoute', 'actionFour'],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'pattern' => '/grouped/{section}',
                    'placeholders' => [
                        'section' => '[A-Za-z]+',
                    ],
                    'middleware' => ['groupedMiddleware'],
                    'routes' => [
                        [
                            'methods' => ['GET'],
                            'pattern' => '/two/{id}',
                            'middleware' => ['twoMiddleware'],
                            'invokable' => ['TwoRoute', 'actionTwo'],
                        ],
                        [
                            'methods' => ['GET'],
                            'pattern' => '/three/{id}',
                            'placeholders' => [
                                'id' => '\d+',
                            ],
                            'invokable' => ['ThreeRoute', 'actionThree'],
                        ],
                    ],
                ],
                [
                    'name' => 'one',
                    'priority' => -10,
                    'methods' => ['GET', 'POST'],
                    'pattern' => '/one/{id}',
                    'placeholders' => [
                        'id' => 'numeric',
                    ],
                    'middleware' => ['oneMiddleware'],
                    'invokable' => ['OneRoute', 'actionOne'],
                ],
            ]));
        /* @var LoaderInterface $loader */

        $driver = new DefinitionFileDriver($loader);

        /* @var RouteMetadata[] $routes */
        $routes = $driver->getRoutingMetadata([]);

        $route = $routes[0];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals(['abstract', 'grouped'], $route->getPrefixes());
        self::assertEquals('four', $route->getName());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals('/abstract/dependent/four', $route->getPattern());
        self::assertEquals([], $route->getPlaceholders());
        self::assertEquals(['fourMiddleware', 'dependentMiddleware', 'abstractMiddleware'], $route->getMiddleware());
        self::assertEquals(['FourRoute', 'actionFour'], $route->getInvokable());

        $route = $routes[1];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals([], $route->getPrefixes());
        self::assertEquals('', $route->getName());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals('/grouped/{section}/two/{id}', $route->getPattern());
        self::assertEquals(['section' => '[A-Za-z]+'], $route->getPlaceholders());
        self::assertEquals(['twoMiddleware', 'groupedMiddleware'], $route->getMiddleware());
        self::assertEquals(['TwoRoute', 'actionTwo'], $route->getInvokable());

        $route = $routes[2];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals([], $route->getPrefixes());
        self::assertEquals('', $route->getName());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals('/grouped/{section}/three/{id}', $route->getPattern());
        self::assertEquals(['section' => '[A-Za-z]+', 'id' => '\d+'], $route->getPlaceholders());
        self::assertEquals(['groupedMiddleware'], $route->getMiddleware());
        self::assertEquals(['ThreeRoute', 'actionThree'], $route->getInvokable());

        $route = $routes[3];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals([], $route->getPrefixes());
        self::assertEquals('one', $route->getName());
        self::assertEquals(-10, $route->getPriority());
        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals('/one/{id}', $route->getPattern());
        self::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        self::assertEquals(['oneMiddleware'], $route->getMiddleware());
        self::assertEquals(['OneRoute', 'actionOne'], $route->getInvokable());
    }
}
