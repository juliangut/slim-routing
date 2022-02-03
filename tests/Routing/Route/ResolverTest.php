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

namespace Jgut\Slim\Routing\Tests\Route;

use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * Routing resolver tests.
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
    protected function setUp(): void
    {
        $this->resolver = new Resolver(new Configuration());
    }

    /**
     * @dataProvider routeNameProvider
     *
     * @param RouteMetadata $route
     * @param string        $name
     */
    public function testRouteName(RouteMetadata $route, string $name = null)
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
            [new RouteMetadata(), null],
            [(new RouteMetadata())->setName('name'), 'name'],
            [
                (new RouteMetadata())
                    ->setName('name')
                    ->setGroup((new GroupMetadata())->setPrefix('prefix')),
                'prefix_name',
            ],
        ];
    }

    /**
     * @dataProvider routeMiddlewareProvider
     *
     * @param RouteMetadata $route
     * @param array         $middleware
     */
    public function testRouteMiddleware(RouteMetadata $route, array $middleware)
    {
        self::assertEquals($middleware, $this->resolver->getMiddleware($route));
    }

    /**
     * Route name checker provider.
     *
     * @return array
     */
    public function routeMiddlewareProvider(): array
    {
        return [
            [new RouteMetadata(), []],
            [(new RouteMetadata())->setMiddleware(['routeMiddleware']), ['routeMiddleware']],
            [
                (new RouteMetadata())
                    ->setMiddleware(['routeMiddleware'])
                    ->setGroup((new GroupMetadata())->setMiddleware(['groupMiddleware'])),
                ['routeMiddleware', 'groupMiddleware'],
            ],
        ];
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
            [new RouteMetadata(), '/'],
            [
                (new RouteMetadata())
                    ->setPattern('entity/{id}'),
                '/entity/{id}',
            ],
            [
                (new RouteMetadata())
                    ->setPattern('entity/{id}')
                    ->setGroup((new GroupMetadata())->setPattern('parent/{section}')),
                '/parent/{section}/entity/{id}',
            ],
            [
                (new RouteMetadata())
                    ->setPattern('/{path}/to/entity/{id}')
                    ->setPlaceholders(['path' => '[a-z]+', 'id' => 'alnum']),
                '/{path:[a-z]+}/to/entity/{id:[a-zA-Z0-9]+}',
            ],
            [
                (new RouteMetadata())
                    ->setPattern('entity/{id}')
                    ->setPlaceholders(['id' => 'alnum']),
                '/entity/{id:[a-zA-Z0-9]+}',
            ],
            [
                (new RouteMetadata())
                    ->setPattern('entity/{id}')
                    ->setPlaceholders(['id' => '[a-z]+'])
                    ->setGroup(
                        (new GroupMetadata())
                            ->setPattern('parent/{section}')
                            ->setPlaceholders(['section' => 'any'])
                    ),
                '/parent/{section:[^}]+}/entity/{id:[a-z]+}',
            ],
        ];
    }

    public function testDuplicatedParameter()
    {
        $this->expectExceptionMessage('There are duplicated route parameters: id');
        $this->expectException(\RuntimeException::class);
        $route = (new RouteMetadata())
            ->setPattern('entity/{id}')
            ->setGroup((new GroupMetadata())->setPattern('parent/{id}'));

        $this->resolver->getPattern($route);
    }

    public function testInvalidPlaceholder()
    {
        $this->expectExceptionMessage('Placeholder "~245" is not a known alias or a valid regex pattern');
        $this->expectException(\InvalidArgumentException::class);
        $route = (new RouteMetadata())->setPlaceholders(['id', '~245']);

        $this->resolver->getPattern($route);
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

    public function testDuplicatedRouteName()
    {
        $this->expectExceptionMessage('There are duplicated route names: route, name');
        $this->expectException(\RuntimeException::class);
        $routes = [
            (new RouteMetadata())->setName('route'),
            (new RouteMetadata())->setName('route'),
            (new RouteMetadata())->setName('name'),
            (new RouteMetadata())->setName('name'),
        ];

        $this->resolver->checkDuplicatedRoutes($routes);
    }

    public function testDuplicatedRoutePath()
    {
        $this->expectExceptionMessage('There are duplicated routes: GET /route/{[a-zA-Z0-9]+}');
        $this->expectException(\RuntimeException::class);
        $nonDuplicatedRoutes = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('{path}/to/route/{id}')
                ->setPlaceholders(['path' => 'alpha', 'id' => 'numeric']),
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('{path}/to/route')
                ->setPlaceholders(['path' => 'alpha']),
        ];

        $this->resolver->checkDuplicatedRoutes($nonDuplicatedRoutes);

        $duplicatedRoutes = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('route/{id}')
                ->setPlaceholders(['id' => 'alnum']),
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('route/{slug}')
                ->setPlaceholders(['slug' => 'alnum']),
        ];

        $this->resolver->checkDuplicatedRoutes($duplicatedRoutes);
    }
}
