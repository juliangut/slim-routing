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

use Jgut\Mapping\Driver\DriverFactoryInterface;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Route\RouteResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\SimpleCache\CacheInterface;
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
     * Metadata cache.
     *
     * @var CacheInterface|null
     */
    protected $cache;

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
     * Set cache.
     *
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
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
     * {@inheritdoc}
     */
    public function lookupRoute(string $identifier): RouteInterface
    {
        if ($this->routesRegistered === false) {
            $this->registerRoutes();
        }

        return parent::lookupRoute($identifier);
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
            $arguments = $resolver->getArguments($routeMetadata);
            if (\count($arguments) !== 0) {
                $route->setArguments($arguments);
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
     * @return RouteMetadata[]
     */
    protected function getRoutesMetadata(): array
    {
        $mappingSources = $this->configuration->getSources();
        $cacheKey = $this->getCacheKey($mappingSources);

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        /** @var RouteMetadata[] $routes */
        $routes = $this->configuration->getMetadataResolver()->getMetadata($mappingSources);

        $routeResolver = $this->configuration->getRouteResolver();
        $routeResolver->checkDuplicatedRoutes($routes);

        $routes = $routeResolver->sort($routes);

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $routes);
        }

        return $routes;
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
            $metadata->getMethods(),
            $resolver->getPattern($metadata),
            $metadata->getInvokable(),
            $metadata
        );

        $this->routes[$route->getIdentifier()] = $route;
        $this->routeCounter++;

        return $route;
    }

    /**
     * @param mixed[] $methods
     * @param string  $pattern
     * @param mixed   $handler
     *
     * @return RouteInterface
     */
    final protected function createRoute(array $methods, string $pattern, $handler): RouteInterface
    {
        return $this->createMetadataRoute($methods, $pattern, $handler);
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
        ?RouteMetadata $metadata = null
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

    /**
     * Get cache key.
     *
     * @param mixed[] $mappingSources
     *
     * @return string
     */
    protected function getCacheKey(array $mappingSources): string
    {
        $key = \implode(
            '.',
            \array_map(
                function ($mappingSource): string {
                    if (!\is_array($mappingSource)) {
                        $mappingSource = [
                            'type' => DriverFactoryInterface::DRIVER_ANNOTATION,
                            'path' => $mappingSource,
                        ];
                    }

                    $path = \is_array($mappingSource['path'])
                        ? \implode('', $mappingSource['path'])
                        : $mappingSource['path'];

                    return $mappingSource['type'] . '.' . $path;
                },
                $mappingSources
            )
        );

        return \sha1($key);
    }
}
