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

use InvalidArgumentException;
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\RouteResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class RouteResolverTest extends TestCase
{
    protected RouteResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new RouteResolver(new Configuration());
    }

    /**
     * @dataProvider routeNameProvider
     */
    public function testRouteName(RouteMetadata $route, ?string $name = null): void
    {
        static::assertEquals($name, $this->resolver->getName($route));
    }

    /**
     * Route name checker provider.
     *
     * @return array<array{RouteMetadata, string|null}>
     */
    public static function routeNameProvider(): array
    {
        return [
            [new RouteMetadata('callable'), null],
            [
                (new RouteMetadata('callable'))->setName('name'),
                'name',
            ],
            [
                (new RouteMetadata('callable'))->setName('name')
                    ->setGroup((new GroupMetadata())->setPrefix('prefix')),
                'prefix_name',
            ],
        ];
    }

    /**
     * @dataProvider routeMiddlewareProvider
     *
     * @param array<string> $middleware
     */
    public function testRouteMiddleware(RouteMetadata $route, array $middleware): void
    {
        static::assertEquals($middleware, $this->resolver->getMiddleware($route));
    }

    /**
     * Route name checker provider.
     *
     * @return array<array{RouteMetadata, array<string>}>
     */
    public static function routeMiddlewareProvider(): array
    {
        return [
            [new RouteMetadata('callable'), []],
            [
                (new RouteMetadata('callable'))
                    ->setMiddlewares(['routeMiddleware']),
                ['routeMiddleware'],
            ],
            [
                (new RouteMetadata('callable'))
                    ->setMiddlewares(['routeMiddleware'])
                    ->setGroup((new GroupMetadata())->setMiddlewares(['groupMiddleware'])),
                ['routeMiddleware', 'groupMiddleware'],
            ],
        ];
    }

    /**
     * @dataProvider routePatternProvider
     */
    public function testRoutePattern(Configuration $configuration, RouteMetadata $route, string $result): void
    {
        static::assertEquals($result, (new RouteResolver($configuration))->getPattern($route));
    }

    /**
     * Route path checker provider.
     */
    public static function routePatternProvider(): array
    {
        return [
            [new Configuration(), new RouteMetadata('callable'), '/'],
            [new Configuration(['trailingSlash' => true]), new RouteMetadata('callable'), '/'],
            [
                new Configuration(),
                (new RouteMetadata('callable'))
                    ->setPattern('entity/{id}/'),
                '/entity/{id}',
            ],
            [
                new Configuration(['trailingSlash' => true]),
                (new RouteMetadata('callable'))
                    ->setPattern('entity/{id}')
                    ->setGroup((new GroupMetadata())->setPattern('parent/{section}')),
                '/parent/{section}/entity/{id}/',
            ],
            [
                new Configuration(),
                (new RouteMetadata('callable'))
                    ->setPattern('/{path}/to/entity/{id}')
                    ->setPlaceholders(['path' => '[a-z]+', 'id' => 'alnum']),
                '/{path:[a-z]+}/to/entity/{id:[a-zA-Z0-9]+}',
            ],
            [
                new Configuration(['trailingSlash' => true]),
                (new RouteMetadata('callable'))
                    ->setPattern('entity/{id}')
                    ->setPlaceholders(['id' => 'alnum']),
                '/entity/{id:[a-zA-Z0-9]+}/',
            ],
            [
                new Configuration(),
                (new RouteMetadata('callable'))
                    ->setPattern('entity/{id}')
                    ->setPlaceholders(['id' => '[a-z]+'])
                    ->setGroup(
                        (new GroupMetadata())
                            ->setPattern('parent/{section}')
                            ->setPlaceholders(['section' => 'any']),
                    ),
                '/parent/{section:[^}]+}/entity/{id:[a-z]+}',
            ],
            [
                new Configuration(),
                (new RouteMetadata('callable'))
                    ->setPattern('entity/{id}')
                    ->setPlaceholders(['id' => '[a-z]+', 'section' => '[0-9]+'])
                    ->setGroup(
                        (new GroupMetadata())
                            ->setPattern('parent/{section}')
                            ->setPlaceholders(['section' => 'any']),
                    ),
                '/parent/{section:[0-9]+}/entity/{id:[a-z]+}',
            ],
        ];
    }

    public function testDuplicatedParameter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There are duplicated route parameters: id');

        $route = (new RouteMetadata('callable'))
            ->setPattern('entity/{id}')
            ->setGroup((new GroupMetadata())->setPattern('parent/{id}'));

        $this->resolver->getPattern($route);
    }

    public function testInvalidPlaceholder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Placeholder "~245" is not a known alias or a valid regex pattern');

        $route = (new RouteMetadata('callable'))->setPlaceholders(['id', '~245']);

        $this->resolver->getPattern($route);
    }

    /**
     * @dataProvider routeArgumentsProvider
     *
     * @param array<mixed> $result
     */
    public function testRouteArguments(Configuration $configuration, RouteMetadata $route, array $result): void
    {
        static::assertEquals($result, (new RouteResolver($configuration))->getArguments($route));
    }

    /**
     * Route arguments provider.
     */
    public static function routeArgumentsProvider(): array
    {
        return [
            [new Configuration(), new RouteMetadata('callable'), []],
            [
                new Configuration(),
                (new RouteMetadata('callable'))->setArguments(['routeArgument' => 'value']),
                ['routeArgument' => 'value'],
            ],
            [
                new Configuration(),
                (new RouteMetadata('callable'))
                    ->setArguments(['routeArgument' => 'route'])
                    ->setGroup((new GroupMetadata())->setArguments(['groupArgument' => 'group'])),
                ['groupArgument' => 'group', 'routeArgument' => 'route'],
            ],
            [
                new Configuration(),
                (new RouteMetadata('callable'))
                    ->setArguments(['routeArgument' => 'route', 'groupArgument' => 'replaced'])
                    ->setGroup((new GroupMetadata())->setArguments(['groupArgument' => 'group'])),
                ['groupArgument' => 'replaced', 'routeArgument' => 'route'],
            ],
        ];
    }

    public function testRouteSorting(): void
    {
        $routes = [
            (new RouteMetadata('callable'))->setPriority(10),
            new RouteMetadata('callable'),
            (new RouteMetadata('callable'))->setPriority(-10),
        ];

        $sortedRoutes = $this->resolver->sort($routes);

        static::assertEquals(-10, $sortedRoutes[0]->getPriority());
        static::assertEquals(0, $sortedRoutes[1]->getPriority());
        static::assertEquals(10, $sortedRoutes[2]->getPriority());
    }

    public function testDuplicatedRouteName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There are duplicated route names: route, name');

        $routes = [
            (new RouteMetadata('callable'))->setName('route'),
            (new RouteMetadata('callable'))->setName('route'),
            (new RouteMetadata('callable'))->setName('name'),
            (new RouteMetadata('callable'))->setName('name'),
        ];

        $this->resolver->checkDuplicatedRoutes($routes);
    }

    public function testDuplicatedRoutePath(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There are duplicated routes: GET /route/{[a-zA-Z0-9]+}');

        $nonDuplicatedRoutes = [
            (new RouteMetadata('callable'))
                ->setMethods(['GET'])
                ->setPattern('{path}/to/route/{id}')
                ->setPlaceholders(['path' => 'alpha', 'id' => 'numeric']),
            (new RouteMetadata('callable'))
                ->setMethods(['GET'])
                ->setPattern('{path}/to/route')
                ->setPlaceholders(['path' => 'alpha']),
        ];

        $this->resolver->checkDuplicatedRoutes($nonDuplicatedRoutes);

        $duplicatedRoutes = [
            (new RouteMetadata('callable'))
                ->setMethods(['GET'])
                ->setPattern('route/{id}')
                ->setPlaceholders(['id' => 'alnum']),
            (new RouteMetadata('callable'))
                ->setMethods(['GET'])
                ->setPattern('route/{slug}')
                ->setPlaceholders(['slug' => 'alnum']),
        ];

        $this->resolver->checkDuplicatedRoutes($duplicatedRoutes);
    }
}
