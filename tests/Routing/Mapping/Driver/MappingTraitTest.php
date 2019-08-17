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
    public function testEmptyMethods(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Route methods can not be empty');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['methods' => ''],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    public function testInvalidMethods(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Route methods must be a string or string array. "integer" given');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['methods' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    public function testInvalidPlaceholders(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Placeholder keys must be all strings');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['placeholders' => ['invalid']],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    public function testInvalidMiddleware(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Middleware must be a string or string array. "integer" given');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['middleware' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    public function testMissingInvocable(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Route invocable definition missing');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    public function testInvalidInvocable(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Route invocable does not seam to be supported by Slim router');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['invocable' => 10],
            ]));
        /* @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver */

        $driver->getMetadata();
    }

    public function testInvalidParameters(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Parameters keys must be all strings');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
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
        $driver->expects(static::once())
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
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertEquals('four', $route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('FourRoute' . ':' . 'actionFour', $route->getinvocable());
        static::assertEquals('four', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals(['fourMiddleware'], $route->getMiddleware());

        $route = $routes[1];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertNull($route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('TwoRoute' . ':' . 'actionTwo', $route->getinvocable());
        static::assertEquals('two/{id}', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals(['twoMiddleware'], $route->getMiddleware());

        $route = $routes[2];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertEquals('', $route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals('ThreeRoute' . ':' . 'actionThree', $route->getinvocable());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('three/{id}', $route->getPattern());
        static::assertEquals(['id' => '\d+'], $route->getPlaceholders());
        static::assertEquals([], $route->getMiddleware());

        $route = $routes[3];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertEquals('one', $route->getName());
        static::assertEquals(['GET', 'POST'], $route->getMethods());
        static::assertEquals(-10, $route->getPriority());
        static::assertEquals('OneRoute' . ':' . 'actionOne', $route->getinvocable());
        static::assertEquals('one/{id}', $route->getPattern());
        static::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        static::assertEquals(['oneMiddleware'], $route->getMiddleware());
        static::assertEquals('fake_transformer', $route->getTransformer());
        static::assertEquals(['id' => 'int'], $route->getParameters());
    }
}
