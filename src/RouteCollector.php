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

use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Route\RouteResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteCollector as SlimRouteCollector;

/**
 * Route loader collector.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RouteCollector extends SlimRouteCollector
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
    private $routesRegistered = false;

    /**
     * RouteCollector constructor.
     *
     * @param Configuration                    $configuration
     * @param ResponseFactoryInterface         $responseFactory
     * @param CallableResolverInterface        $callableResolver
     * @param ContainerInterface|null          $container
     * @param InvocationStrategyInterface|null $defaultInvocationStrategy
     * @param RouteParserInterface|null        $routeParser
     * @param string|null                      $cacheFile
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Configuration $configuration,
        ResponseFactoryInterface $responseFactory,
        CallableResolverInterface $callableResolver,
        ?ContainerInterface $container = null,
        ?InvocationStrategyInterface $defaultInvocationStrategy = null,
        ?RouteParserInterface $routeParser = null,
        ?string $cacheFile = null
    ) {
        parent::__construct(
            $responseFactory,
            $callableResolver,
            $container,
            $defaultInvocationStrategy,
            $routeParser,
            $cacheFile
        );

        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): array
    {
        if ($this->routesRegistered === false) {
            $this->registerRoutes();
        }

        return $this->routes;
    }

    /**
     * Register routes.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    final public function registerRoutes(): void
    {
        $routes = $this->routes;
        $this->routes = [];

        $resolver = $this->configuration->getRouteResolver();

        foreach ($this->getRoutesMetadata() as $routeMetadata) {
            $route = $this->mapMetadataRoute($routeMetadata, $resolver);

            $name = $resolver->getName($routeMetadata);
            if ($name !== null) {
                $route->setName($name);
            }

            foreach ($resolver->getMiddleware($routeMetadata) as $middleware) {
                $route->add($middleware);
            }
        }

        $this->routes = \array_merge($this->routes, $routes);

        $this->routesRegistered = true;
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
     * @param RouteResolver $resolver
     *
     * @return Route
     */
    protected function mapMetadataRoute(RouteMetadata $metadata, RouteResolver $resolver): Route
    {
        $route = $this->createMetadataRoute(
            \array_map('strtoupper', $metadata->getMethods()),
            $resolver->getPattern($metadata),
            $metadata->getInvocable(),
            $metadata
        );

        $this->routes[$route->getIdentifier()] = $route;
        $this->routeCounter++;

        return $route;
    }

    /**
     * @param mixed[] $methods
     * @param string  $pattern
     * @param mixed   $callable
     *
     * @return RouteInterface
     */
    final protected function createRoute(array $methods, string $pattern, $callable): RouteInterface
    {
        return $this->createMetadataRoute($methods, $pattern, $callable);
    }

    /**
     * Create new metadata aware route.
     *
     * @param string[]           $methods
     * @param string             $pattern
     * @param mixed              $callable
     * @param RouteMetadata|null $metadata
     *
     * @return Route
     */
    protected function createMetadataRoute(
        array $methods,
        string $pattern,
        $callable,
        RouteMetadata $metadata = null
    ): RouteInterface {
        return new Route(
            $methods,
            $pattern,
            $callable,
            $this->responseFactory,
            $this->callableResolver,
            $metadata,
            $this->container,
            $this->defaultInvocationStrategy,
            $this->routeGroups,
            $this->routeCounter
        );
    }
}