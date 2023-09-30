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

use Jgut\Mapping\Driver\DriverFactoryInterface;
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Route\RouteResolver;
use Jgut\Slim\Routing\RouteCollector;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteInterface;

/**
 * @internal
 */
class RouteCollectorTest extends TestCase
{
    public function testSlimRouteMappingSupported(): void
    {
        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();

        $routeCollector = new RouteCollector(new Configuration(), $responseFactory, $callableResolver);

        $route = $routeCollector->map(['GET'], '/', '');

        static::assertInstanceOf(Route::class, $route);
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals('/', $route->getPattern());
        static::assertEquals('', $route->getCallable());
    }

    public static function sourcesProvider(): iterable
    {
        yield [[__DIR__ . '/Mapping/Files/Classes/Valid/Attribute']];

        yield [
            [
                [
                    'type' => DriverFactoryInterface::DRIVER_ANNOTATION,
                    'path' => [__DIR__ . '/Mapping/Files/Classes/Valid/Annotation'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider sourcesProvider
     *
     * @param list<string|array<string, mixed>> $sources
     */
    public function testRoutes(array $sources): void
    {
        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();
        $cache->expects(static::once())
            ->method('has')
            ->willReturn(false);
        $cache->expects(static::once())
            ->method('set');

        $routesMetadata = [
            (new RouteMetadata(['one', 'action'], null))
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setXmlHttpRequest(true),
            (new RouteMetadata(['two', 'action'], 'two'))
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setMiddleware(['route-middleware']),
        ];

        $configuration = new Configuration(['sources' => $sources]);

        $resolver = $this->getMockBuilder(RouteResolver::class)
            ->setConstructorArgs([$configuration])
            ->getMock();
        $resolver->expects(static::once())
            ->method('sort')
            ->willReturn($routesMetadata);

        $configuration->setRouteResolver($resolver);

        $routeCollector = new RouteCollector($configuration, $responseFactory, $callableResolver);
        $routeCollector->setCache($cache);

        static::assertCount(2, $routeCollector->getRoutes());
    }

    /**
     * @dataProvider sourcesProvider
     *
     * @param list<string|array<string, mixed>> $sources
     */
    public function testCachedRoutes($sources): void
    {
        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();

        $routesMetadata = [
            (new RouteMetadata(['one', 'action'], null))
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setXmlHttpRequest(true),
            (new RouteMetadata(['two', 'action'], 'two'))
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setMiddleware(['route-middleware']),
        ];

        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();
        $cache->expects(static::once())
            ->method('has')
            ->willReturn(true);
        $cache->expects(static::once())
            ->method('get')
            ->with(static::matchesRegularExpression('/^prefix_.+$/'))
            ->willReturn($routesMetadata);

        $routeCollector = new RouteCollector(
            new Configuration(['sources' => $sources]),
            $responseFactory,
            $callableResolver,
        );
        $routeCollector->setCache($cache);
        $routeCollector->setCachePrefix('prefix_');

        static::assertCount(2, $routeCollector->getRoutes());
    }

    /**
     * @dataProvider sourcesProvider
     *
     * @param list<string|array<string, mixed>> $sources
     */
    public function testRouteLookup($sources): void
    {
        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();

        $routesMetadata = [
            (new RouteMetadata(['one', 'action'], null))
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setXmlHttpRequest(true),
            (new RouteMetadata(['two', 'action'], 'two'))
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setArguments(['scope' => 'public'])
                ->setMiddleware(['route-middleware']),
        ];

        $configuration = new Configuration(['sources' => $sources]);

        $resolver = $this->getMockBuilder(RouteResolver::class)
            ->setConstructorArgs([$configuration])
            ->onlyMethods(['sort'])
            ->getMock();
        $resolver->expects(static::once())
            ->method('sort')
            ->willReturn($routesMetadata);

        $configuration->setRouteResolver($resolver);

        $routeCollector = new RouteCollector($configuration, $responseFactory, $callableResolver);

        $resolvedRoute = $routeCollector->lookupRoute('route0');
        static::assertInstanceOf(RouteInterface::class, $resolvedRoute);
        static::assertNull($resolvedRoute->getName());
        static::assertEquals([], $resolvedRoute->getArguments());

        $resolvedRoute = $routeCollector->lookupRoute('route1');
        static::assertInstanceOf(RouteInterface::class, $resolvedRoute);
        static::assertEquals('two', $resolvedRoute->getName());
        static::assertEquals(['scope' => 'public'], $resolvedRoute->getArguments());
    }
}
