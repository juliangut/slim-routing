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

use Jgut\Mapping\Driver\DriverInterface;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class AbstractDriverTestCase extends TestCase
{
    public function checkResources(DriverInterface $driver, string $basePath): void
    {
        $routes = $driver->getMetadata();

        $route = $routes[0];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertInstanceOf(GroupMetadata::class, $route->getGroup());
        static::assertEquals('four', $route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals($basePath . '\DependentRoute:actionFour', $route->getInvokable());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('four', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals([], $route->getArguments());
        static::assertFalse($route->isXmlHttpRequest());
        static::assertEquals(['fourMiddleware'], $route->getMiddleware());

        $route = $routes[1];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertNull($route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals($basePath . '\GroupedRoute:actionTwo', $route->getInvokable());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals('two/{id}', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals(['scope' => 'protected'], $route->getArguments());
        static::assertEquals([], $route->getParameters());
        static::assertFalse($route->isXmlHttpRequest());
        static::assertEquals(['twoMiddleware'], $route->getMiddleware());

        $route = $routes[2];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertNull($route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals($basePath . '\GroupedRoute:actionThree', $route->getInvokable());
        static::assertEquals(10, $route->getPriority());
        static::assertEquals('three/{id}', $route->getPattern());
        static::assertEquals(['id' => '\d+'], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals([], $route->getParameters());
        static::assertTrue($route->isXmlHttpRequest());
        static::assertEquals([], $route->getMiddleware());

        $route = $routes[3];
        static::assertInstanceOf(RouteMetadata::class, $route);
        static::assertEquals('one', $route->getName());
        static::assertEquals(['GET', 'POST'], $route->getMethods());
        static::assertEquals($basePath . '\SingleRoute:actionOne', $route->getInvokable());
        static::assertEquals(-10, $route->getPriority());
        static::assertEquals('one/{id}', $route->getPattern());
        static::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        static::assertEquals([], $route->getArguments());
        static::assertEquals(['first' => 'value', 'id' => 'int'], $route->getParameters());
        static::assertTrue($route->isXmlHttpRequest());
        static::assertEquals(['oneMiddleware'], $route->getMiddleware());
        static::assertEquals('fake_transformer', $route->getTransformer());
    }
}
