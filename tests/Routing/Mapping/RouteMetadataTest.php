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

use Jgut\Slim\Routing\Mapping\RouteMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Route metadata tests.
 */
class RouteMetadataTest extends TestCase
{
    /**
     * @var RouteMetadata
     */
    protected $route;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->route = new RouteMetadata();
    }

    public function testDefaults()
    {
        self::assertEquals([], $this->route->getPrefixes());
        self::assertEquals('', $this->route->getName());
        self::assertEquals([], $this->route->getMethods());
        self::assertEquals(0, $this->route->getPriority());
        self::assertEquals('', $this->route->getPattern());
        self::assertEquals([], $this->route->getPlaceholders());
        self::assertEquals([], $this->route->getMiddleware());
        self::assertNull($this->route->getInvokable());
    }

    public function testPrefixes()
    {
        $prefixes = ['one', 'two'];

        $this->route->setPrefixes($prefixes);

        self::assertEquals($prefixes, $this->route->getPrefixes());
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route invokable does not seem to be supported by Slim router
     */
    public function testInvalidInvokable()
    {
        $this->route->setInvokable(10);
    }

    public function testSetState()
    {
        $route = RouteMetadata::__set_state([
            'name' => 'route',
            'priority' => 10,
            'methods' => ['GET'],
            'pattern' => '',
            'placeholders' => [],
            'middleware' => [],
            'invokable' => null,
        ]);

        self::assertInstanceOf(RouteMetadata::class, $route);
        self::assertEquals('route', $route->getName());
    }
}
