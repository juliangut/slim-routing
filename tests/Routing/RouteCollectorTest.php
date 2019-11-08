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
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Route\RouteResolver;
use Jgut\Slim\Routing\RouteCollector;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
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
        /* @var ResponseFactoryInterface $responseFactory */
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        /** @var CallableResolverInterface $callableResolver */
        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        /* @var Configuration $configuration */

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
        /* @var ResponseFactoryInterface $responseFactory */
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
        /* @var RouteResolver $resolver */

        $configuration->expects(static::any())
            ->method('getRouteResolver')
            ->will($this->returnValue($resolver));
        /* @var Configuration $configuration */

        $router = new RouteCollector($configuration, $responseFactory, $callableResolver);

        static::assertCount(2, $router->getRoutes());
    }

    public function testRouteLookup(): void
    {
        $responseFactory = $this->getMockBuilder(ResponseFactoryInterface::class)
            ->getMock();
        /* @var ResponseFactoryInterface $responseFactory */
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
        /* @var RouteResolver $resolver */

        $configuration->expects(static::any())
            ->method('getRouteResolver')
            ->will($this->returnValue($resolver));
        /* @var Configuration $configuration */

        $router = new RouteCollector($configuration, $responseFactory, $callableResolver);

        $resolvedRoute = $router->lookupRoute('route1');
        static::assertInstanceOf(RouteInterface::class, $resolvedRoute);
        static::assertEquals('two', $resolvedRoute->getName());
    }
}
