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

namespace Jgut\Slim\Routing\Tests\Compiler;

use Jgut\Slim\Routing\Compiler\AnnotationCompiler;
use Jgut\Slim\Routing\Route;
use Jgut\Slim\Routing\Tests\Files\Annotation\CircularReferenceRoute;
use Jgut\Slim\Routing\Tests\Files\Annotation\DependentRoute;
use Jgut\Slim\Routing\Tests\Files\Annotation\DuplicatedPlaceholderRoute;
use Jgut\Slim\Routing\Tests\Files\Annotation\GroupedRoute;
use Jgut\Slim\Routing\Tests\Files\Annotation\NoRoutesRoute;
use Jgut\Slim\Routing\Tests\Files\Annotation\SingleRoute;
use Jgut\Slim\Routing\Tests\Files\Annotation\UnknownGroupRoute;
use Jgut\Slim\Routing\Tests\Files\Annotation\UnknownPlaceholdersRoute;
use PHPUnit\Framework\TestCase;

/**
 * Annotation compiler tests.
 */
class AnnotationCompilerTest extends TestCase
{
    /**
     * @var AnnotationCompiler
     */
    protected $compiler;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->compiler = new AnnotationCompiler();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /Class .+ does not define any route$/
     */
    public function testNoRoutesRoute()
    {
        $this->compiler->getRoutes([NoRoutesRoute::class]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Pattern ".+" contains duplicated placeholders$/
     */
    public function testDuplicatedPlaceholderRoute()
    {
        $this->compiler->getRoutes([DuplicatedPlaceholderRoute::class]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Pattern ".+" does not contain the following placeholders: .+$/
     */
    public function testUnknownPlaceholderRoute()
    {
        $this->compiler->getRoutes([UnknownPlaceholdersRoute::class]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Referenced group "unknown" on class .+ is not defined$/
     */
    public function testUnknownGroupRoute()
    {
        $this->compiler->getRoutes([UnknownGroupRoute::class]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Circular reference detected with group "circular" on class .+$/
     */
    public function testCircularReferenceRoute()
    {
        $this->compiler->getRoutes([CircularReferenceRoute::class]);
    }

    public function testSingleRoute()
    {
        /* @var Route[] $routes */
        $routes = $this->compiler->getRoutes([SingleRoute::class]);

        self::assertCount(1, $routes);

        $route = $routes[0];

        self::assertInstanceOf(Route::class, $route);
        self::assertEquals('one', $route->getName());
        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals(-10, $route->getPriority());
        self::assertEquals('/one/{id}', $route->getPattern());
        self::assertEquals(['id' => '[0-9]+'], $route->getPlaceholders());
        self::assertEquals(['oneMiddleware'], $route->getMiddleware());
        self::assertEquals([SingleRoute::class, 'actionOne'], $route->getInvokable());
    }

    public function testGroupedRoute()
    {
        /* @var Route[] $routes */
        $routes = $this->compiler->getRoutes([GroupedRoute::class]);

        self::assertCount(1, $routes);

        $route = $routes[0];

        self::assertInstanceOf(Route::class, $route);
        self::assertEquals(['GET'], $route->getMethods());
        self::assertEquals('/grouped/{section}/two/{id}', $route->getPattern());
        self::assertEquals(['section' => '[A-Za-z]+'], $route->getPlaceholders());
        self::assertEquals(['twoMiddleware', 'groupedMiddleware'], $route->getMiddleware());
        self::assertEquals([GroupedRoute::class, 'actionTwo'], $route->getInvokable());
    }

    public function testDependentRoute()
    {
        /* @var Route[] $routes */
        $routes = $this->compiler->getRoutes([
            GroupedRoute::class,
            DependentRoute::class,
        ]);

        self::assertCount(2, $routes);

        $route = $routes[1];

        self::assertInstanceOf(Route::class, $route);
        self::assertEquals('/grouped/{section}/dependent/three', $route->getPattern());
        self::assertEquals(['section' => '[A-Za-z]+'], $route->getPlaceholders());
        self::assertEquals(['threeMiddleware', 'dependentMiddleware', 'groupedMiddleware'], $route->getMiddleware());
        self::assertEquals([DependentRoute::class, 'actionThree'], $route->getInvokable());
    }
}
