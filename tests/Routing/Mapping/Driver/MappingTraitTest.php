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

        $driver->getMetadata();
    }

    public function testMissingInvokable(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Route invokable definition missing');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [],
            ]));
        // @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver

        $driver->getMetadata();
    }

    public function testInvalidInvokable(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Route invokable does not seam to be supported by Slim router');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                ['invokable' => 10],
            ]));

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
                    'invokable' => 'callable',
                    'transformer' => 'fake_transformer',
                    'parameters' => ['invalid'],
                ],
            ]));

        $driver->getMetadata();
    }

    public function testInvalidArguments(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\DriverException::class);
        $this->expectExceptionMessage('Arguments keys must be all strings');

        $driver = $this->getMockForTrait(MappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->will($this->returnValue([
                [
                    'invokable' => 'callable',
                    'transformer' => 'fake_transformer',
                    'arguments' => ['invalid'],
                ],
            ]));
        // @var \Jgut\Mapping\Driver\AbstractMappingDriver $driver

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
                                    'invokable' => 'FourRoute' . ':' . 'actionFour',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'middleware' => ['groupedMiddleware'],
                    'routes' => [
                        [
                            'methods' => ['GET'],
                            'pattern' => '/two/{id}',
                            'arguments' => [
                                'scope' => 'protected',
                            ],
                            'middleware' => ['twoMiddleware'],
                            'invokable' => 'TwoRoute' . ':' . 'actionTwo',
                        ],
                        [
                            'methods' => ['GET'],
                            'pattern' => '/three/{id}',
                            'placeholders' => [
                                'id' => '\d+',
                            ],
                            'invokable' => 'ThreeRoute' . ':' . 'actionThree',
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
                    'invokable' => 'OneRoute' . ':' . 'actionOne',
                ],
            ]));

        /** @var RouteMetadata[] $routes */
        $routes = $driver->getMetadata();

        $route = $routes[0];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertEquals('four', $route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('FourRoute' . ':' . 'actionFour', $route->getInvokable());
        static::assertEquals('four', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals(['fourMiddleware'], $route->getMiddleware());

        $route = $routes[1];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertNull($route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('TwoRoute' . ':' . 'actionTwo', $route->getInvokable());
        static::assertEquals('two/{id}', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals(['scope' => 'protected'], $route->getArguments());
        static::assertEquals(['twoMiddleware'], $route->getMiddleware());

        $route = $routes[2];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertEquals('', $route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals('ThreeRoute' . ':' . 'actionThree', $route->getInvokable());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('three/{id}', $route->getPattern());
        static::assertEquals(['id' => '\d+'], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals([], $route->getMiddleware());

        $route = $routes[3];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertEquals('one', $route->getName());
        static::assertEquals(['GET', 'POST'], $route->getMethods());
        static::assertEquals(-10, $route->getPriority());
        static::assertEquals('OneRoute' . ':' . 'actionOne', $route->getInvokable());
        static::assertEquals('one/{id}', $route->getPattern());
        static::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals(['oneMiddleware'], $route->getMiddleware());
        static::assertEquals('fake_transformer', $route->getTransformer());
        static::assertEquals(['id' => 'int'], $route->getParameters());
    }
}
