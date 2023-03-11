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

namespace Jgut\Slim\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\CallableResolver;
use Slim\Factory\AppFactory as SlimAppFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;

class AppFactory extends SlimAppFactory
{
    protected static ?Configuration $configuration;

    public static function create(
        ?ResponseFactoryInterface $responseFactory = null,
        ?ContainerInterface $container = null,
        ?CallableResolverInterface $callableResolver = null,
        ?RouteCollectorInterface $routeCollector = null,
        ?RouteResolverInterface $routeResolver = null,
        ?MiddlewareDispatcherInterface $middlewareDispatcher = null
    ): App {
        static::$responseFactory = $responseFactory ?? static::$responseFactory;

        $responseFactory = self::determineResponseFactory();
        $container ??= static::$container;
        $callableResolver ??= static::getCallableResolver($container);

        return new App(
            $responseFactory,
            $container,
            $callableResolver,
            $routeCollector ?? static::getRouteCollector($responseFactory, $callableResolver, $container),
            $routeResolver ?? static::$routeResolver,
            $middlewareDispatcher ?? static::$middlewareDispatcher,
        );
    }

    protected static function getCallableResolver(?ContainerInterface $container = null): CallableResolverInterface
    {
        return static::$callableResolver ?? new CallableResolver($container);
    }

    protected static function getRouteCollector(
        ResponseFactoryInterface $responseFactory,
        CallableResolverInterface $callableResolver,
        ?ContainerInterface $container = null
    ): RouteCollectorInterface {
        $configuration = static::$configuration ?? new Configuration();

        return static::$routeCollector
            ?? new RouteCollector($configuration, $responseFactory, $callableResolver, $container);
    }

    final public static function setRouteCollectorConfiguration(Configuration $configuration): void
    {
        static::$configuration = $configuration;
    }
}
