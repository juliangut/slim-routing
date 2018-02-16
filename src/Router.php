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
    protected $routesLoaded = false;

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
    public function getRoutes()
    {
        if ($this->routesLoaded === false) {
            $routes = $this->routes;
            $this->routes = [];

            $this->registerRoutes();

            $this->routes = \array_merge($this->routes, $routes);

            $this->routesLoaded = true;
        }

        return parent::getRoutes();
    }

    /**
     * Register routes.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function registerRoutes()
    {
        $resolver = $this->configuration->getRouteResolver();

        foreach ($this->getRoutesMetadata() as $route) {
            /** @var Route $slimRoute */
            $slimRoute = $this->map($route->getMethods(), $resolver->getPattern($route), $route->getInvokable());

            $name = $resolver->getName($route);
            if ($name !== null) {
                $slimRoute->setName($name);
            }

            foreach ($resolver->getMiddleware($route) as $middleware) {
                $slimRoute->add($middleware);
            }
        }
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
     * {@inheritdoc}
     */
    protected function createRoute($methods, $pattern, $callable): Route
    {
        $route = new Route($methods, $pattern, $callable, $this->routeGroups, $this->routeCounter);

        $route->setConfiguration($this->configuration);

        if ($this->container !== null) {
            $route->setContainer($this->container);
        }

        $route->setOutputBuffering($this->container->get('settings')['outputBuffering']);

        return $route;
    }
}
