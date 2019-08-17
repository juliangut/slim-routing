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

namespace Jgut\Slim\Routing\Tests\Mapping\Driver;

use Jgut\Slim\Routing\Mapping\Driver\MappingTrait;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Definition file mapping driver factory tests.
 */
class MappingTraitTest extends TestCase
{
    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Route methods can not be empty
     */
    public function testEmptyMethods(): void
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['methods' => ''],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Route methods must be a string or string array. "integer" given
     */
    public function testInvalidMethods(): void
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['methods' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Placeholder keys must be all strings
     */
    public function testInvalidPlaceholders(): void
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['placeholders' => ['invalid']],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Middleware must be a string or string array. "integer" given
     */
    public function testInvalidMiddleware(): void
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['middleware' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Route invocable definition missing
     */
    public function testMissinginvocable(): void
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Route invocable does not seam to be supported by Slim router
     */
    public function testInvalidinvocable(): void
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['invocable' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    /**
     * @expectedException \Jgut\Mapping\Exception\DriverException
     * @expectedExceptionMessage Parameters keys must be all strings
     */
    public function testInvalidParameters(): void
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [
                    'invocable' => 'invocable',
                    'transformer' => 'fake_transformer',
                    'parameters' => ['invalid'],
                ],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    public function testRoutes(): void
    {
        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects($this->once())
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
                                    'invocable' => 'FourRoute' . ':' . 'actionFour',
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
                            'invocable' => 'TwoRoute' . ':' . 'actionTwo',
                        ],
                        [
                            'methods' => ['GET'],
                            'pattern' => '/three/{id}',
                            'placeholders' => [
                                'id' => '\d+',
                            ],
                            'invocable' => 'ThreeRoute' . ':' . 'actionThree',
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
                    'transformer' => 'fake_transformer',
                    'parameters' => [
                        'id' => 'int',
                    ],
                    'middleware' => ['oneMiddleware'],
                    'invocable' => 'OneRoute' . ':' . 'actionOne',
                ],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $routes = $driver->getMetadata();

        $route = $routes[0];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals('four', $route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals('FourRoute' . ':' . 'actionFour', $route->getinvocable());
        self::assertEquals('four', $route->getPattern());
        self::assertEquals([], $route->getPlaceholders());
        self::assertEquals(['fourMiddleware'], $route->getMiddleware());

        $route = $routes[1];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertNull($route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals('TwoRoute' . ':' . 'actionTwo', $route->getinvocable());
        self::assertEquals('two/{id}', $route->getPattern());
        self::assertEquals([], $route->getPlaceholders());
        self::assertEquals(['twoMiddleware'], $route->getMiddleware());

        $route = $routes[2];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals('', $route->getName());
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals('ThreeRoute' . ':' . 'actionThree', $route->getinvocable());
        self::assertEquals(0, $route->getPriority());
        self::assertEquals('three/{id}', $route->getPattern());
        self::assertEquals(['id' => '\d+'], $route->getPlaceholders());
        self::assertEquals([], $route->getMiddleware());

        $route = $routes[3];
        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals('one', $route->getName());
        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals(-10, $route->getPriority());
        self::assertEquals('OneRoute' . ':' . 'actionOne', $route->getinvocable());
        self::assertEquals('one/{id}', $route->getPattern());
        self::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        self::assertEquals(['oneMiddleware'], $route->getMiddleware());
        self::assertEquals('fake_transformer', $route->getTransformer());
        self::assertEquals(['id' => 'int'], $route->getParameters());
    }
}
