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
        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();

        $routeCollector = new RouteCollector($configuration, $responseFactory, $callableResolver);

        $route = $routeCollector->map(['GET'], '/', '');

        static::assertInstanceOf(Route::class, $route);
        static::assertEquals(['GET'], $route->getMethods());
        static::assertEquals('/', $route->getPattern());
        static::assertEquals('', $route->getCallable());
    }

    public function testRoutes(): void
    {
        $sources = \PHP_VERSION_ID < 80_000
            ? [
                [
                    'type' => DriverFactoryInterface::DRIVER_ANNOTATION,
                    'path' => [__DIR__ . '/Mapping/Files/Classes/Valid/Annotation'],
                ],
            ]
            : [__DIR__ . '/Mapping/Files/Classes/Valid/Attribute'];

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getSources', 'getRouteResolver'])
            ->getMock();
        $configuration->expects(static::once())
            ->method('getSources')
            ->willReturn($sources);
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
                ->setMiddleware(['twoMiddleware']),
        ];

        $resolver = $this->getMockBuilder(RouteResolver::class)
            ->setConstructorArgs([$configuration])
            ->setMethods(['sort'])
            ->getMock();
        $resolver->expects(static::once())
            ->method('sort')
            ->willReturn($routesMetadata);

        $configuration
            ->method('getRouteResolver')
            ->willReturn($resolver);

        $routeCollector = new RouteCollector($configuration, $responseFactory, $callableResolver);
        $routeCollector->setCache($cache);

        static::assertCount(2, $routeCollector->getRoutes());
    }

    public function testCachedRoutes(): void
    {
        $sources = \PHP_VERSION_ID < 80_000
            ? [
                [
                    'type' => DriverFactoryInterface::DRIVER_ANNOTATION,
                    'path' => [__DIR__ . '/Mapping/Files/Classes/Valid/Annotation'],
                ],
            ]
            : [__DIR__ . '/Mapping/Files/Classes/Valid/Attribute'];

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        /** @var CallableResolverInterface $callableResolver */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        $configuration->expects(static::once())
            ->method('getSources')
            ->willReturn($sources);

        $routesMetadata = [
            (new RouteMetadata(['one', 'action'], null))
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setXmlHttpRequest(true),
            (new RouteMetadata(['two', 'action'], 'two'))
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setMiddleware(['twoMiddleware']),
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

        $routeCollector = new RouteCollector($configuration, $responseFactory, $callableResolver);
        $routeCollector->setCache($cache);
        $routeCollector->setCachePrefix('prefix_');

        static::assertCount(2, $routeCollector->getRoutes());
    }

    public function testRouteLookup(): void
    {
        $sources = \PHP_VERSION_ID < 80_000
            ? [
                [
                    'type' => DriverFactoryInterface::DRIVER_ANNOTATION,
                    'path' => [__DIR__ . '/Mapping/Files/Classes/Valid/Annotation'],
                ],
            ]
            : [__DIR__ . '/Mapping/Files/Classes/Valid/Attribute'];

        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        /** @var CallableResolverInterface $callableResolver */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getSources', 'getRouteResolver'])
            ->getMock();
        $configuration->expects(static::once())
            ->method('getSources')
            ->willReturn($sources);

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
                ->setMiddleware(['twoMiddleware']),
        ];

        $resolver = $this->getMockBuilder(RouteResolver::class)
            ->setConstructorArgs([$configuration])
            ->setMethods(['sort'])
            ->getMock();
        $resolver->expects(static::once())
            ->method('sort')
            ->willReturn($routesMetadata);

        $configuration
            ->method('getRouteResolver')
            ->willReturn($resolver);

        $router = new RouteCollector($configuration, $responseFactory, $callableResolver);

        $resolvedRoute = $router->lookupRoute('route0');
        static::assertInstanceOf(RouteInterface::class, $resolvedRoute);
        static::assertNull($resolvedRoute->getName());
        static::assertEquals([], $resolvedRoute->getArguments());

        $resolvedRoute = $router->lookupRoute('route1');
        static::assertInstanceOf(RouteInterface::class, $resolvedRoute);
        static::assertEquals('two', $resolvedRoute->getName());
        static::assertEquals(['scope' => 'public'], $resolvedRoute->getArguments());
    }
}
