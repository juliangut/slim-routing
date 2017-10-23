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

use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use Jgut\Slim\Routing\Mapping\Driver\DriverInterface;
use Psr\Container\ContainerInterface;
use Slim\Route;

/**
 * Routing manager.
 */
class Manager
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
     * Routing Manager constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get route resolver.
     *
     * @return Resolver
     */
    public function getResolver(): Resolver
    {
        if (!$this->resolver) {
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
     * Register routes into Slim router.
     *
     * @param ContainerInterface $container
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function registerRoutes(ContainerInterface $container)
    {
        $routes = $this->getRoutesMetadata();
        if (!count($routes)) {
            throw new \RuntimeException('There are no defined routes');
        }

        /* @var \Slim\Router $router */
        $router = $container->get('router');
        $buffering = $container->get('settings')['outputBuffering'];
        $resolver = $this->getResolver();

        foreach ($routes as $route) {
            $methods = $route->getMethods();
            $pattern = $resolver->getPattern($route);
            $callable = $route->getInvokable();

            /* @var Route $slimRoute */
            $slimRoute = $router->map($methods, $pattern, $callable);
            $slimRoute->setContainer($container);
            $slimRoute->setOutputBuffering($buffering);

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
     * Get routes.
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        $resolver = $this->getResolver();

        $routes = [];

        foreach ($this->getRoutesMetadata() as $route) {
            $methods = $route->getMethods();
            $pattern = $resolver->getPattern($route);
            $callable = $route->getInvokable();

            $slimRoute = new Route($methods, $pattern, $callable);

            $name = $resolver->getName($route);
            if ($name !== null) {
                $slimRoute->setName($name);
            }

            foreach ($resolver->getMiddleware($route) as $middleware) {
                $slimRoute->add($middleware);
            }

            $routes[] = $slimRoute;
        }

        return $routes;
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

        $routes = $resolver->sort(count($routes) ? array_merge(...$routes) : []);

        $resolver->checkDuplicatedRoutes($routes);

        return $routes;
    }
}
