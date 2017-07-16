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
use PHPUnit\Framework\TestCase;

/**
 * Route tests.
 */
class RouteTest extends TestCase
{
    /**
     * @var Route
     */
    protected $route;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->route = new Route();
    }

    public function testDefaults()
    {
        self::assertEquals('', $this->route->getName());
        self::assertEquals([], $this->route->getMethods());
        self::assertEquals(0, $this->route->getPriority());
        self::assertEquals('', $this->route->getPattern());
        self::assertEquals([], $this->route->getPlaceholders());
        self::assertEquals([], $this->route->getMiddleware());
        self::assertNull($this->route->getInvokable());
    }

    public function testName()
    {
        $this->route->setName('route');

        self::assertEquals('route', $this->route->getName());
    }

    public function testMethods()
    {
        $methods = ['GET', 'POST', 'DELETE'];

        $this->route->setMethods($methods);

        self::assertEquals($methods, $this->route->getMethods());
    }

    public function testPriority()
    {
        $this->route->setPriority(-10);

        self::assertEquals(-10, $this->route->getPriority());
    }

    public function testPath()
    {
        $path = '/home/route/path/{id}';

        $this->route->setPattern($path);

        self::assertEquals($path, $this->route->getPattern());
    }

    public function testPlaceholders()
    {
        $placeholders = ['id' => '[0-9]{5}'];

        $this->route->setPlaceholders($placeholders);

        self::assertEquals($placeholders, $this->route->getPlaceholders());
    }

    public function testMiddleware()
    {
        $middleware = ['middlewareOne', 'middlewareTwo'];

        $this->route->setMiddleware($middleware);

        self::assertEquals($middleware, $this->route->getMiddleware());
    }

    public function testInvokable()
    {
        $callable = ['containerKey', 'method'];

        $this->route->setInvokable($callable);

        self::assertEquals($callable, $this->route->getInvokable());
    }

    public function testSetState()
    {
        $route = Route::__set_state([
            'name' => 'route',
            'priority' => 10,
            'methods' => ['GET'],
            'pattern' => '',
            'placeholders' => [],
            'middleware' => [],
            'invokable' => null,
        ]);

        self::assertInstanceOf(Route::class, $route);
        self::assertEquals('route', $route->getName());
    }
}
