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

namespace Jgut\Slim\Routing\Mapping\Driver;

use Jgut\Slim\Routing\Mapping\Loader\LoaderInterface;
use Jgut\Slim\Routing\Mapping\RouteMetadata;

/**
 * Definition file mapping driver.
 */
class DefinitionFileDriver implements DriverInterface
{
    /**
     * Mapping loader.
     *
     * @var LoaderInterface
     */
    protected $mappingLoader;

    /**
     * FileDriver constructor.
     *
     * @param LoaderInterface $mappingLoader
     */
    public function __construct(LoaderInterface $mappingLoader)
    {
        $this->mappingLoader = $mappingLoader;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getRoutingMetadata(array $loadingPaths): array
    {
        return $this->getRoutesMetadata($this->getRoutingData($loadingPaths));
    }

    /**
     * Get routes metadata.
     *
     * @param array $routingSource
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return RouteMetadata[]
     */
    protected function getRoutesMetadata(array $routingSource): array
    {
        $routes = [];

        foreach ($routingSource as $source) {
            $routes[] = array_key_exists('routes', $source)
                ? $this->getGroupRoutesMetadata($source)
                : [$this->getRouteMetadata($source)];
        }

        return count($routes) ? array_merge(...$routes) : [];
    }

    /**
     * Get group routes metadata.
     *
     * @param array $routingGroup
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return RouteMetadata[]
     */
    protected function getGroupRoutesMetadata(array $routingGroup): array
    {
        $groupPrefixes = $this->getSourcePrefix($routingGroup);
        $groupPattern = $this->getSourcePattern($routingGroup);
        $groupPlaceholders = $this->getSourcePlaceholders($routingGroup);
        $groupMiddleware = $this->getSourceMiddleware($routingGroup);

        return array_map(
            function (RouteMetadata $routeMetadata) use (
                $groupPrefixes,
                $groupPattern,
                $groupPlaceholders,
                $groupMiddleware
            ) {
                $routeMetadata->setPrefixes(array_merge($groupPrefixes, $routeMetadata->getPrefixes()));
                $routeMetadata->setPattern($groupPattern . $routeMetadata->getPattern());
                $routeMetadata->setPlaceholders(array_merge($groupPlaceholders, $routeMetadata->getPlaceholders()));
                $routeMetadata->setMiddleware(array_merge($routeMetadata->getMiddleware(), $groupMiddleware));

                return $routeMetadata;
            },
            $this->getRoutesMetadata($routingGroup['routes'])
        );
    }

    /**
     * Get route metadata.
     *
     * @param array $routingRoute
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return RouteMetadata
     */
    protected function getRouteMetadata(array $routingRoute): RouteMetadata
    {
        return (new RouteMetadata())
            ->setName($this->getSourceName($routingRoute))
            ->setPriority($this->getSourcePriority($routingRoute))
            ->setMethods($this->getSourceMethods($routingRoute))
            ->setPattern($this->getSourcePattern($routingRoute))
            ->setPlaceholders($this->getSourcePlaceholders($routingRoute))
            ->setMiddleware($this->getSourceMiddleware($routingRoute))
            ->setInvokable($this->getSourceInvokable($routingRoute));
    }

    /**
     * Get source prefix.
     *
     * @param array $source
     *
     * @return string[]
     */
    protected function getSourcePrefix(array $source): array
    {
        return array_key_exists('prefix', $source) ? [trim($source['prefix'])] : [];
    }

    /**
     * Get source name.
     *
     * @param array $source
     *
     * @return string
     */
    protected function getSourceName(array $source): string
    {
        return array_key_exists('name', $source) ? trim($source['name']) : '';
    }

    /**
     * Get source methods.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getSourceMethods(array $source): array
    {
        if (!array_key_exists('methods', $source)) {
            return ['GET'];
        }

        $methods = [];

        $sourceMethods = $source['methods'];
        if (!is_array($sourceMethods)) {
            $sourceMethods = [$sourceMethods];
        }

        foreach (array_filter($sourceMethods) as $method) {
            if (!is_string($method)) {
                throw new \InvalidArgumentException(
                    sprintf('Route methods must be a string or string array. "%s" given', gettype($method))
                );
            }

            $methods[] = strtoupper(trim($method));
        }

        $methods = array_unique(array_filter($methods, 'strlen'));

        if (!count($methods)) {
            throw new \InvalidArgumentException('Route methods can not be empty');
        }

        return $methods;
    }

    /**
     * Get source priority.
     *
     * @param array $source
     *
     * @return int
     */
    protected function getSourcePriority(array $source): int
    {
        return array_key_exists('priority', $source) ? (int) $source['priority'] : 0;
    }

    /**
     * Get source pattern.
     *
     * @param array $source
     *
     * @return string
     */
    protected function getSourcePattern(array $source): string
    {
        return array_key_exists('pattern', $source) && trim($source['pattern']) !== '/'
            ? '/' . trim($source['pattern'], ' /')
            : '';
    }

    /**
     * Get source placeholders.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getSourcePlaceholders(array $source): array
    {
        if (!array_key_exists('placeholders', $source)) {
            return [];
        }

        $placeholders = $source['placeholders'];

        array_map(
            function ($key) {
                if (!is_string($key)) {
                    throw new \InvalidArgumentException('Placeholder keys must be all strings');
                }
            },
            array_keys($placeholders)
        );

        return $placeholders;
    }

    /**
     * Get source middleware.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getSourceMiddleware(array $source): array
    {
        if (!array_key_exists('middleware', $source)) {
            return [];
        }

        $middlewareList = $source['middleware'];
        if (!is_array($middlewareList)) {
            $middlewareList = [$middlewareList];
        }

        foreach ($middlewareList as $middleware) {
            if (!is_string($middleware)) {
                throw new \InvalidArgumentException(
                    sprintf('Route middleware must be a string or string array. "%s" given', gettype($middleware))
                );
            }
        }

        return $middlewareList;
    }

    /**
     * Get source invokable.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     *
     * @return callable
     */
    protected function getSourceInvokable(array $source)
    {
        if (!array_key_exists('invokable', $source)) {
            throw new \InvalidArgumentException('Route invokable definition missing');
        }

        $invokable = $source['invokable'];

        if (!is_string($invokable) && !is_array($invokable) && !is_callable($invokable)) {
            throw new \InvalidArgumentException('Route invokable does not seam to be supported by Slim router');
        }

        return $invokable;
    }

    /**
     * Get routing data.
     *
     * @param array $loadingPaths
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    final protected function getRoutingData(array $loadingPaths): array
    {
        return array_map(
            function ($source) {
                if (!is_array($source)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Routing definition must be an array. "%s" given',
                        is_object($source) ? get_class($source) : gettype($source)
                    ));
                }

                return $source;
            },
            $this->mappingLoader->getMappingData($loadingPaths)
        );
    }
}
