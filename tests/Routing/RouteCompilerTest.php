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

namespace Jgut\Slim\Routing\Tests;

use Jgut\Slim\Routing\Route;
use Jgut\Slim\Routing\RouteCompiler;
use PHPUnit\Framework\TestCase;

/**
 * Routing compiler tests.
 */
class RouteCompilerTest extends TestCase
{
    /**
     * @var RouteCompiler
     */
    protected $compiler;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->compiler = new RouteCompiler();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Routing definition must be an array
     */
    public function testInvalidRoute()
    {
        $routes = [''];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route methods must be a string or string array. "integer" given
     */
    public function testInvalidMethodsTypeRoute()
    {
        $routes = [
            [
                'methods' => 10,
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route methods can not be empty
     */
    public function testEmptyMethodsRoute()
    {
        $routes = [
            [
                'methods' => '',
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route "ANY" method cannot be defined with other methods
     */
    public function testAnyMethodMisusedRoute()
    {
        $routes = [
            [
                'methods' => ['GET', 'ANY'],
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Placeholder keys must be all strings
     */
    public function testInvalidPlaceholderKeyRoute()
    {
        $routes = [
            [
                'placeholders' => ['placeholder'],
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Placeholder pattern "noRegex/" is not a valid regex
     */
    public function testInvalidPlaceholdersRegexRoute()
    {
        $routes = [
            [
                'placeholders' => ['id' => 'noRegex/'],
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Pattern ".+" contains duplicated placeholders$/
     */
    public function testDuplicatedPlaceholderRoute()
    {
        $routes = [
            [
                'pattern' => '/path/{duplicated}/placeholders/{duplicated}',
                'invokable' => 'invokable',
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Pattern ".+" does not contain the following placeholders: .+$/
     */
    public function testUnknownPlaceholderRoute()
    {
        $routes = [
            [
                'pattern' => '/only/{one}/placeholder',
                'placeholders' => [
                    'one' => 'a-z',
                    'two' => 'unknown',
                ],
                'invokable' => 'invokable',
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Route invokable definition missing
     */
    public function testMissingInvokableRoute()
    {
        $routes = [[]];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route invokable does not seam to be supported by Slim router
     */
    public function testInvalidInvokableTypeRoute()
    {
        $routes = [
            [
                'invokable' => 10,
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Middleware must be a string or string array. "integer" given
     */
    public function testInvalidMiddlewareRoute()
    {
        $routes = [
            [
                'middleware' => 10,
            ],
        ];

        $this->compiler->getRoutes($routes);
    }

    public function testSingleRoute()
    {
        $routes = [
            [
                'name' => 'one',
                'methods' => ['GET', 'POST'],
                'priority' => -10,
                'pattern' => '/one/{id}',
                'placeholders' => [
                    'id' => '[0-9]+',
                ],
                'middleware' => ['oneMiddleware'],
                'invokable' => ['class', 'method'],
            ],
        ];

        /* @var Route[] $routes */
        $routes = $this->compiler->getRoutes($routes);

        self::assertCount(1, $routes);

        $route = $routes[0];

        self::assertInstanceOf(Route::class, $route);
        self::assertEquals('one', $route->getName());
        self::assertEquals(['GET', 'POST'], $route->getMethods());
        self::assertEquals(-10, $route->getPriority());
        self::assertEquals('/one/{id}', $route->getPattern());
        self::assertEquals(['id' => '[0-9]+'], $route->getPlaceholders());
        self::assertEquals(['oneMiddleware'], $route->getMiddleware());
        self::assertEquals(['class', 'method'], $route->getInvokable());
    }

    public function testGroupedRoute()
    {
        $routes = [
            [
                'pattern' => '/grouped/{section}',
                'placeholders' => [
                    'section' => '[A-Za-z]+',
                ],
                'middleware' => 'groupedMiddleware',
                'routes' => [
                    [
                        'methods' => 'ANY',
                        'pattern' => '/two/{id}',
                        'middleware' => ['twoMiddleware'],
                        'invokable' => ['class', 'method'],
                    ],
                ],
            ],
        ];

        /* @var Route[] $routes */
        $routes = $this->compiler->getRoutes($routes);

        self::assertCount(1, $routes);

        $route = $routes[0];

        self::assertInstanceOf(Route::class, $route);
        self::assertEquals(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $route->getMethods());
        self::assertEquals('/grouped/{section}/two/{id}', $route->getPattern());
        self::assertEquals(['section' => '[A-Za-z]+'], $route->getPlaceholders());
        self::assertEquals(['twoMiddleware', 'groupedMiddleware'], $route->getMiddleware());
        self::assertEquals(['class', 'method'], $route->getInvokable());
    }
}
