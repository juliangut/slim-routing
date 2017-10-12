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

use Jgut\Slim\Routing\Mapping\RouteMetadata;
use Jgut\Slim\Routing\Mapping\Source\SourceFactory;
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
        /* @var \Slim\Router $router */
        $router = $container->get('router');
        $buffering = $container->get('settings')['outputBuffering'];

        $resolver = $this->getResolver();

        foreach ($this->getRoutesMetadata() as $routeMetadata) {
            $methods = $resolver->getMethods($routeMetadata);
            $pattern = $resolver->getPattern($routeMetadata);
            $callable = $routeMetadata->getInvokable();

            /* @var \Slim\Route $slimRoute */
            $slimRoute = $router->map($methods, $pattern, $callable);
            $slimRoute->setContainer($container);
            $slimRoute->setOutputBuffering($buffering);

            $name = $resolver->getName($routeMetadata);
            if ($name !== '') {
                $slimRoute->setName($name);
            }

            foreach ($routeMetadata->getMiddleware() as $middleware) {
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

        foreach ($this->getRoutesMetadata() as $routeMetadata) {
            $methods = $resolver->getMethods($routeMetadata);
            $pattern = $resolver->getPattern($routeMetadata);
            $callable = $routeMetadata->getInvokable();

            $slimRoute = new Route($methods, $pattern, $callable);

            $name = $resolver->getName($routeMetadata);
            if ($name !== '') {
                $slimRoute->setName($name);
            }

            foreach ($routeMetadata->getMiddleware() as $middleware) {
                $slimRoute->add($middleware);
            }

            $routes[] = $slimRoute;
        }

        return $routes;
    }

    /**
     * Get routes metadata.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return RouteMetadata[]
     */
    protected function getRoutesMetadata(): array
    {
        $routesMetadata = [];
        foreach ($this->configuration->getSources() as $source) {
            $source = SourceFactory::getSource($source);

            $routesMetadata[] = $source->getRoutingMetadata();
        }

        $resolver = $this->getResolver();

        $routesMetadata = $resolver->sort(count($routesMetadata) ? array_merge(...$routesMetadata) : []);

        $resolver->checkDuplicatedRoutes($routesMetadata);

        return $routesMetadata;
    }
}
