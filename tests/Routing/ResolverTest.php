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

use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Mapping\RouteMetadata;
use Jgut\Slim\Routing\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * Routing compiler tests.
 */
class ResolverTest extends TestCase
{
    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->resolver = new Resolver(new Configuration());
    }

    /**
     * @dataProvider routeNameProvider
     *
     * @param RouteMetadata $route
     * @param string        $name
     */
    public function testRouteName(RouteMetadata $route, string $name)
    {
        self::assertEquals($name, $this->resolver->getName($route));
    }

    /**
     * Route name checker provider.
     *
     * @return array
     */
    public function routeNameProvider(): array
    {
        return [
            [new RouteMetadata(), ''],
            [(new RouteMetadata())->setName('name'), 'name'],
            [(new RouteMetadata())->setPrefixes(['one', 'two'])->setName('name'), 'one_two_name'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Route "ANY" method cannot be defined with other methods
     */
    public function testInvalidRouteMethods()
    {
        $route = (new RouteMetadata())->setMethods(['POST', 'ANY', 'GET']);

        $this->resolver->getMethods($route);
    }

    public function testRouteMethodsAny()
    {
        $route = (new RouteMetadata())->setMethods(['ANY']);

        self::assertEquals(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $this->resolver->getMethods($route));
    }

    public function testRouteMethod()
    {
        $route = (new RouteMetadata())->setMethods(['GET', 'POST']);

        self::assertEquals(['GET', 'POST'], $this->resolver->getMethods($route));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Placeholder pattern "~245" is not a known alias or a valid regex
     */
    public function testInvalidMethodsTypeRoute()
    {
        $route = (new RouteMetadata())->setPlaceholders(['id', '~245']);

        $this->resolver->getPattern($route);
    }

    /**
     * @dataProvider routePathProvider
     *
     * @param RouteMetadata $route
     * @param string        $pattern
     */
    public function testRoutePattern(RouteMetadata $route, string $pattern)
    {
        self::assertEquals($pattern, $this->resolver->getPattern($route));
    }

    /**
     * Route path checker provider.
     *
     * @return array
     */
    public function routePathProvider(): array
    {
        return [
            [
                (new RouteMetadata())
                    ->setPattern('/entity/{id}'),
                '/entity/{id}',
            ],
            [
                (new RouteMetadata())
                    ->setPattern('/entity/{id}')
                    ->setPlaceholders(['id' => 'alnum']),
                '/entity/{id:[a-zA-Z0-9]+}',
            ],
            [
                (new RouteMetadata())
                    ->setPattern('/entity/{id}')
                    ->setPlaceholders(['id' => '[a-z+]']),
                '/entity/{id:[a-z+]}',
            ],
        ];
    }

    public function testRouteSorting()
    {
        $routes = [
            (new RouteMetadata())->setPriority(10),
            new RouteMetadata(),
            (new RouteMetadata())->setPriority(-10),
        ];

        $sortedRoutes = $this->resolver->sort($routes);

        self::assertEquals(-10, $sortedRoutes[0]->getPriority());
        self::assertEquals(0, $sortedRoutes[1]->getPriority());
        self::assertEquals(10, $sortedRoutes[2]->getPriority());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There are duplicated route names: route, name
     */
    public function testDuplicatedRouteName()
    {
        $routes = [
            (new RouteMetadata())->setName('route'),
            (new RouteMetadata())->setName('route'),
            (new RouteMetadata())->setName('name'),
            (new RouteMetadata())->setName('name'),
        ];

        $this->resolver->checkDuplicatedRoutes($routes);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There are duplicated routes: GET /route/{[a-zA-Z0-9]+}
     */
    public function testDuplicatedRoutePath()
    {
        $routes = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('/route/{id}')
                ->setPlaceholders(['id' => 'alnum']),
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('/route/{slug}')
                ->setPlaceholders(['slug' => 'alnum']),
        ];

        $this->resolver->checkDuplicatedRoutes($routes);
    }
}
