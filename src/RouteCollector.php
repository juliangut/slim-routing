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

use InvalidArgumentException;
use Jgut\Mapping\Driver\DriverFactoryInterface;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Route\RouteResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteCollector as SlimRouteCollector;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RouteCollector extends SlimRouteCollector
{
    protected Configuration $configuration;

    protected ?CacheInterface $cache = null;

    protected string $cachePrefix = '';

    private bool $routesRegistered = false;

    public function __construct(
        Configuration $configuration,
        ResponseFactoryInterface $responseFactory,
        CallableResolverInterface $callableResolver,
        ?ContainerInterface $container = null,
        ?InvocationStrategyInterface $invocationStrategy = null,
        ?RouteParserInterface $routeParser = null,
        ?string $cacheFile = null
    ) {
        parent::__construct(
            $responseFactory,
            $callableResolver,
            $container,
            $invocationStrategy,
            $routeParser,
            $cacheFile,
        );

        $this->configuration = $configuration;
    }

    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function setCachePrefix(string $cachePrefix): void
    {
        $this->cachePrefix = $cachePrefix;
    }

    public function getRoutes(): array
    {
        if ($this->routesRegistered === false) {
            $this->registerRoutes();
        }

        return $this->routes;
    }

    public function lookupRoute(string $identifier): RouteInterface
    {
        if ($this->routesRegistered === false) {
            $this->registerRoutes();
        }

        return parent::lookupRoute($identifier);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
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

        $this->routes = array_merge($this->routes, $routes);

        $this->routesRegistered = true;
    }

    /**
     * @return array<RouteMetadata>
     */
    protected function getRoutesMetadata(): array
    {
        $mappingSources = $this->configuration->getSources();
        $cacheKey = $this->getCacheKey($mappingSources);

        if ($this->cache !== null && $this->cache->has($cacheKey)) {
            $cachedRoutes = $this->cache->get($cacheKey);
            if (\is_array($cachedRoutes)) {
                /** @var array<RouteMetadata> $cachedRoutes */
                return $cachedRoutes;
            }
        }

        /** @var array<RouteMetadata> $routes */
        $routes = $this->configuration->getMetadataResolver()
            ->getMetadata($mappingSources);

        $routeResolver = $this->configuration->getRouteResolver();
        $routeResolver->checkDuplicatedRoutes($routes);

        $routes = $routeResolver->sort($routes);

        if ($this->cache !== null) {
            $this->cache->set($cacheKey, $routes);
        }

        return $routes;
    }

    protected function mapMetadataRoute(RouteMetadata $metadata, RouteResolver $resolver): Route
    {
        $route = $this->createMetadataRoute(
            $metadata->getMethods(),
            $resolver->getPattern($metadata),
            $metadata->getInvokable(),
            $metadata,
        );

        $this->routes[$route->getIdentifier()] = $route;
        ++$this->routeCounter;

        return $route;
    }

    /**
     * @param array<string>                          $methods
     * @param string|array<string>|callable(): mixed $callable
     */
    final protected function createRoute(array $methods, string $pattern, $callable): RouteInterface
    {
        return $this->createMetadataRoute($methods, $pattern, $callable);
    }

    /**
     * @param array<string>                          $methods
     * @param string|array<string>|callable(): mixed $callable
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
            $this->routeCounter,
        );
    }

    /**
     * @param array<mixed> $mappingSources
     */
    protected function getCacheKey(array $mappingSources): string
    {
        $key = implode(
            '.',
            array_map(
                static function ($mappingSource): string {
                    if (!\is_array($mappingSource)) {
                        $mappingSource = [
                            'type' => DriverFactoryInterface::DRIVER_ANNOTATION,
                            'path' => $mappingSource,
                        ];
                    }

                    $path = \is_array($mappingSource['path'])
                        ? implode('', $mappingSource['path'])
                        : $mappingSource['path'];

                    return $mappingSource['type'] . '.' . $path;
                },
                $mappingSources,
            ),
        );

        return $this->cachePrefix . sha1($key);
    }
}
