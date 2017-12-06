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
use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use Jgut\Slim\Routing\Mapping\Driver\DriverInterface;
use Jgut\Slim\Routing\Mapping\Resolver;
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
     * Route resolver.
     *
     * @var Resolver
     */
    protected $resolver;

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
     * Get route resolver.
     *
     * @return Resolver
     */
    public function getResolver(): Resolver
    {
        if ($this->resolver === null) {
            $this->resolver = new Resolver($this->configuration);
        }

        return $this->resolver;
    }

    /**
     * Set route resolver.
     *
     * @param Resolver $resolver
     */
    public function setResolver(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    protected function createDispatcher(): Dispatcher
    {
        if ($this->dispatcher === null
            && $this->routesLoaded === false
            && $this->cacheFile !== false && file_exists($this->cacheFile)
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

            $this->routes = array_merge($this->routes, $routes);

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
        $routes = $this->getRoutesMetadata();

        if (count($routes) > 0) {
            $resolver = $this->getResolver();

            foreach ($routes as $route) {
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
    }

    /**
     * Get routes metadata.
     *
     * @return \Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata[]
     */
    protected function getRoutesMetadata(): array
    {
        $routes = [];
        foreach ($this->configuration->getSources() as $mappingSource) {
            if (!is_array($mappingSource)) {
                $mappingSource = [
                    'type' => DriverInterface::DRIVER_ANNOTATION,
                    'path' => $mappingSource,
                ];
            }

            $routes[] = DriverFactory::getDriver($mappingSource)->getMetadata();
        }
        $resolver = $this->getResolver();

        $routes = $resolver->sort(count($routes) > 0 ? array_merge(...$routes) : []);

        $resolver->checkDuplicatedRoutes($routes);

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    protected function createRoute($methods, $pattern, $callable): Route
    {
        $route = new Route($methods, $pattern, $callable, $this->routeGroups, $this->routeCounter);
        $route->setConfiguration($this->configuration);
        $route->setContainer($this->container);
        $route->setOutputBuffering($this->container->get('settings')['outputBuffering']);

        return $route;
    }
}
