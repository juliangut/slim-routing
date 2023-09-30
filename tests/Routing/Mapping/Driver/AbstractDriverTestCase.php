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
        $group = $route->getGroup();
        static::assertInstanceOf(GroupMetadata::class, $group->getParent());
        static::assertEquals('dependent', $group->getPrefix());
        static::assertEquals('dependent', $group->getPattern());
        static::assertEquals([], $group->getPlaceholders());
        static::assertEquals([], $group->getArguments());
        static::assertEquals([], $group->getParameters());
        static::assertEquals([], $group->getTransformers());
        static::assertEquals(['dependentMiddleware'], $group->getMiddleware());

        static::assertEquals('four', $route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals('four', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals($basePath . '\DependentRoute:actionFour', $route->getInvokable());
        static::assertFalse($route->isXmlHttpRequest());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals([], $route->getArguments());
        static::assertEquals([], $route->getParameters());
        static::assertEquals(['fourMiddleware'], $route->getMiddleware());

        $route = $routes[1];
        $group = $route->getGroup();
        static::assertNull($group->getParent());
        static::assertNull($group->getPrefix());
        static::assertEquals('grouped/{section}', $group->getPattern());
        static::assertEquals(['section' => '[A-Za-z]+'], $group->getPlaceholders());
        static::assertEquals([], $group->getArguments());
        static::assertEquals(['section' => 'string'], $group->getParameters());
        static::assertEquals(['group-transformer'], $group->getTransformers());
        static::assertEquals(['group-middleware'], $group->getMiddleware());

        static::assertNull($route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals('two/{id}', $route->getPattern());
        static::assertEquals([], $route->getPlaceholders());
        static::assertEquals($basePath . '\GroupedRoute:actionTwo', $route->getInvokable());
        static::assertFalse($route->isXmlHttpRequest());
        static::assertEquals(0, $route->getPriority());
        static::assertEquals(['scope' => 'protected'], $route->getArguments());
        static::assertEquals(['id' => 'int'], $route->getParameters());
        static::assertEquals(['route-transformer'], $route->getTransformers());
        static::assertEquals(['route-middleware'], $route->getMiddleware());

        $route = $routes[2];
        $group = $route->getGroup();
        static::assertNull($group->getParent());
        static::assertNull($group->getParent());
        static::assertNull($group->getPrefix());
        static::assertEquals('grouped/{section}', $group->getPattern());
        static::assertEquals(['section' => '[A-Za-z]+'], $group->getPlaceholders());
        static::assertEquals([], $group->getArguments());
        static::assertEquals(['section' => 'string'], $group->getParameters());
        static::assertEquals(['group-transformer'], $group->getTransformers());
        static::assertEquals(['group-middleware'], $group->getMiddleware());

        static::assertNull($route->getName());
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals('three/{id}', $route->getPattern());
        static::assertEquals(['id' => '\d+'], $route->getPlaceholders());
        static::assertEquals($basePath . '\GroupedRoute:actionThree', $route->getInvokable());
        static::assertTrue($route->isXmlHttpRequest());
        static::assertEquals(10, $route->getPriority());
        static::assertEquals([], $route->getArguments());
        static::assertEquals([], $route->getParameters());
        static::assertEquals([], $route->getTransformers());
        static::assertEquals([], $route->getMiddleware());

        $route = $routes[3];
        static::assertNull($route->getGroup());
        static::assertEquals('one', $route->getName());
        static::assertEquals(['GET', 'POST'], $route->getMethods());
        static::assertEquals('one/{id}', $route->getPattern());
        static::assertEquals(['id' => 'numeric'], $route->getPlaceholders());
        static::assertEquals($basePath . '\SingleRoute:actionOne', $route->getInvokable());
        static::assertTrue($route->isXmlHttpRequest());
        static::assertEquals(-10, $route->getPriority());
        static::assertEquals([], $route->getArguments());
        static::assertEquals(['first' => 'value', 'id' => 'int'], $route->getParameters());
        static::assertEquals(['fake_transformer'], $route->getTransformers());
        static::assertEquals(['oneMiddleware'], $route->getMiddleware());
    }
}
