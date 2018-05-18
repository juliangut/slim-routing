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

use FastRoute\Dispatcher;
use FastRoute\RouteParser;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Resolver;
use Jgut\Slim\Routing\Route\Route;
use Slim\Router as SlimRouter;

/**
 * Route loader router.
 */
class Router extends SlimRouter
{
    /**
     * Routing configuration.
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Mapping routes have been loaded.
     *
     * @var bool
     */
    private $routesLoaded = false;

    /**
     * Router constructor.
     *
     * @param Configuration    $configuration
     * @param RouteParser|null $parser
     */
    public function __construct(
        Configuration $configuration,
        RouteParser $parser = null
    ) {
        parent::__construct($parser);

        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    protected function createDispatcher(): Dispatcher
    {
        if ($this->dispatcher === null
            && $this->routesLoaded === false
            && $this->cacheFile !== false && \file_exists($this->cacheFile)
        ) {
            $this->getRoutes();
        }

        return parent::createDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        if ($this->routesLoaded === false) {
            $this->registerRoutes();

            $this->routesLoaded = true;
        }

        return $this->routes;
    }

    /**
     * Register routes.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function registerRoutes()
    {
        $routes = $this->routes;
        $this->routes = [];

        $resolver = $this->configuration->getRouteResolver();

        foreach ($this->getRoutesMetadata() as $route) {
            $slimRoute = $this->mapMetadataRoute($route, $resolver);

            $name = $resolver->getName($route);
            if ($name !== null) {
                $slimRoute->setName($name);
            }

            foreach ($resolver->getMiddleware($route) as $middleware) {
                $slimRoute->add($middleware);
            }
        }

        $this->routes = \array_merge($this->routes, $routes);
    }

    /**
     * Get routes metadata.
     *
     * @return \Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata[]
     */
    protected function getRoutesMetadata(): array
    {
        /** @var \Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata[] $routes */
        $routes = $this->configuration->getMetadataResolver()->getMetadata($this->configuration->getSources());

        $routeResolver = $this->configuration->getRouteResolver();
        $routeResolver->checkDuplicatedRoutes($routes);

        return $routeResolver->sort($routes);
    }

    /**
     * Map new metadata route.
     *
     * @param RouteMetadata $metadata
     * @param Resolver      $resolver
     *
     * @return Route
     */
    protected function mapMetadataRoute(RouteMetadata $metadata, Resolver $resolver): Route
    {
        $pattern = $resolver->getPattern($metadata);
        if (\count($this->routeGroups) !== 0) {
            // @codeCoverageIgnoreStart
            $pattern = $this->processGroups() . $pattern;
            // @codeCoverageIgnoreEnd
        }

        $route = $this->createMetadataRoute(
            \array_map('strtoupper', $metadata->getMethods()),
            $pattern,
            $metadata->getInvokable(),
            $metadata
        );

        $this->routes[$route->getIdentifier()] = $route;
        $this->routeCounter++;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    protected function createRoute($methods, $pattern, $callable): Route
    {
        return $this->createMetadataRoute($methods, $pattern, $callable);
    }

    /**
     * Create new metadata aware route.
     *
     * @param array              $methods
     * @param string             $pattern
     * @param callable           $callable
     * @param RouteMetadata|null $metadata
     *
     * @return Route
     */
    protected function createMetadataRoute(
        array $methods,
        string $pattern,
        $callable,
        RouteMetadata $metadata = null
    ): Route {
        $route = new Route(
            $methods,
            $pattern,
            $callable,
            $this->configuration,
            $metadata,
            $this->routeGroups,
            $this->routeCounter
        );

        if ($this->container !== null) {
            $route->setContainer($this->container);
            $route->setOutputBuffering($this->container->get('settings')['outputBuffering']);
        }

        return $route;
    }
}
