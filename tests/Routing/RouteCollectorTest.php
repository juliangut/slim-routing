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
 * Route loader collector tests.
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
        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getSources', 'getRouteResolver'])
            ->getMock();
        $configuration->expects(static::once())
            ->method('getSources')
            ->will($this->returnValue([__DIR__ . '/Files/Annotation/Valid']));
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();
        $cache->expects($this->once())
            ->method('has')
            ->will($this->returnValue(false));
        $cache->expects($this->once())
            ->method('set');

        $routesMetadata = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setInvokable(['one', 'action'])
                ->setXmlHttpRequest(true),
            (new RouteMetadata())
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setName('two')
                ->setMiddleware(['twoMiddleware'])
                ->setInvokable(['two', 'action']),
        ];

        $resolver = $this->getMockBuilder(RouteResolver::class)
            ->setConstructorArgs([$configuration])
            ->setMethods(['sort'])
            ->getMock();
        $resolver->expects(static::once())
            ->method('sort')
            ->will($this->returnValue($routesMetadata));

        $configuration->expects(static::any())
            ->method('getRouteResolver')
            ->will($this->returnValue($resolver));

        $routeCollector = new RouteCollector($configuration, $responseFactory, $callableResolver);
        $routeCollector->setCache($cache);

        static::assertCount(2, $routeCollector->getRoutes());
    }

    public function testCachedRoutes(): void
    {
        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        /** @var CallableResolverInterface $callableResolver */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        $configuration->expects(static::once())
            ->method('getSources')
            ->will($this->returnValue([
                [
                    'type' => DriverFactoryInterface::DRIVER_ANNOTATION,
                    'path' => [__DIR__ . '/Files/Annotation/Valid'],
                ],
            ]));

        $routesMetadata = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setInvokable(['one', 'action'])
                ->setXmlHttpRequest(true),
            (new RouteMetadata())
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setName('two')
                ->setMiddleware(['twoMiddleware'])
                ->setInvokable(['two', 'action']),
        ];

        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();
        $cache->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));
        $cache->expects($this->once())
            ->method('get')
            ->will($this->returnValue($routesMetadata));

        $routeCollector = new RouteCollector($configuration, $responseFactory, $callableResolver);
        $routeCollector->setCache($cache);

        static::assertCount(2, $routeCollector->getRoutes());
    }

    public function testRouteLookup(): void
    {
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
            ->will($this->returnValue([__DIR__ . '/Files/Annotation/Valid']));

        $routesMetadata = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setInvokable(['one', 'action'])
                ->setXmlHttpRequest(true),
            (new RouteMetadata())
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setName('two')
                ->setArguments(['scope' => 'public'])
                ->setMiddleware(['twoMiddleware'])
                ->setInvokable(['two', 'action']),
        ];

        $resolver = $this->getMockBuilder(RouteResolver::class)
            ->setConstructorArgs([$configuration])
            ->setMethods(['sort'])
            ->getMock();
        $resolver->expects(static::once())
            ->method('sort')
            ->will($this->returnValue($routesMetadata));

        $configuration->expects(static::any())
            ->method('getRouteResolver')
            ->will($this->returnValue($resolver));

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
