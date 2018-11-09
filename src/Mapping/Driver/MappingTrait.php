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

use Jgut\Mapping\Exception\DriverException;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;

trait MappingTrait
{
    /**
     * Get mapped metadata.
     *
     * @return RouteMetadata[]
     */
    public function getMetadata(): array
    {
        return $this->getRoutesMetadata($this->getMappingData());
    }

    /**
     * Get mapping data.
     *
     * @return mixed[]
     */
    abstract protected function getMappingData(): array;

    /**
     * Get routes metadata.
     *
     * @param array              $mappingData
     * @param GroupMetadata|null $group
     *
     * @return RouteMetadata[]
     */
    protected function getRoutesMetadata(array $mappingData, GroupMetadata $group = null): array
    {
        $routes = [];

        foreach ($mappingData as $mapping) {
            $routes[] = \array_key_exists('routes', $mapping)
                ? $this->getRoutesMetadata($mapping['routes'], $this->getGroupMetadata($mapping, $group))
                : [$this->getRouteMetadata($mapping, $group)];
        }

        return \count($routes) > 0 ? \array_merge(...$routes) : [];
    }

    /**
     * Get group metadata.
     *
     * @param array              $mapping
     * @param GroupMetadata|null $parentGroup
     *
     * @return GroupMetadata
     */
    protected function getGroupMetadata(array $mapping, GroupMetadata $parentGroup = null): GroupMetadata
    {
        $group = (new GroupMetadata())
            ->setPlaceholders($this->getPlaceholders($mapping))
            ->setParameters($this->getParameters($mapping))
            ->setMiddleware($this->getMiddleware($mapping));

        $pattern = $this->getPattern($mapping);
        if ($pattern !== null) {
            $group->setPattern($pattern);
        }

        $prefix = $this->getPrefix($mapping);
        if ($prefix !== null) {
            $group->setPrefix($prefix);
        }

        if ($parentGroup !== null) {
            $group->setParent($parentGroup);
        }

        return $group;
    }

    /**
     * Get route metadata.
     *
     * @param array              $mapping
     * @param GroupMetadata|null $group
     *
     * @return RouteMetadata
     */
    protected function getRouteMetadata($mapping, GroupMetadata $group = null): RouteMetadata
    {
        $route = (new RouteMetadata())
            ->setMethods($this->getMethods($mapping))
            ->setXmlHttpRequest($this->isXmlHttpRequest($mapping))
            ->setPriority($this->getPriority($mapping))
            ->setPlaceholders($this->getPlaceholders($mapping))
            ->setMiddleware($this->getMiddleware($mapping))
            ->setInvokable($this->getInvokable($mapping));

        $pattern = $this->getPattern($mapping);
        if ($pattern !== null) {
            $route->setPattern($pattern);
        }

        $name = $this->getName($mapping);
        if ($name !== null) {
            $route->setName($name);
        }

        if ($group !== null) {
            $route->setGroup($group);
        }

        $transformer = $this->getTransformer($mapping);
        if ($transformer !== null) {
            $route->setTransformer($transformer)
                ->setParameters($this->getParameters($mapping));
        }

        return $route;
    }

    /**
     * Get mapping prefix.
     *
     * @param array $mapping
     *
     * @return string|null
     */
    protected function getPrefix(array $mapping)
    {
        return \array_key_exists('prefix', $mapping) && \trim($mapping['prefix']) !== ''
            ? \trim($mapping['prefix'])
            : null;
    }

    /**
     * Get mapping name.
     *
     * @param array $mapping
     *
     * @return string|null
     */
    protected function getName(array $mapping)
    {
        return \array_key_exists('name', $mapping) && \trim($mapping['name']) !== ''
            ? \trim($mapping['name'])
            : null;
    }

    /**
     * Get mapping methods.
     *
     * @param array $mapping
     *
     * @throws DriverException
     *
     * @return string[]
     */
    protected function getMethods(array $mapping): array
    {
        if (!\array_key_exists('methods', $mapping)) {
            return ['GET'];
        }

        $methods = [];

        $mappingMethods = $mapping['methods'];
        if (!\is_array($mappingMethods)) {
            $mappingMethods = [$mappingMethods];
        }

        foreach (\array_filter($mappingMethods) as $method) {
            if (!\is_string($method)) {
                throw new DriverException(
                    \sprintf('Route methods must be a string or string array. "%s" given', \gettype($method))
                );
            }

            $methods[] = \strtoupper(\trim($method));
        }

        $methods = \array_unique(\array_filter($methods, 'strlen'));

        if (\count($methods) === 0) {
            throw new DriverException('Route methods can not be empty');
        }

        return $methods;
    }

    /**
     * Get parameter transformer.
     *
     * @param array $mapping
     *
     * @return string|null
     */
    protected function getTransformer(array $mapping)
    {
        return $mapping['transformer'] ?? null;
    }

    /**
     * Get XmlHttpRequest constraint.
     *
     * @param array $mapping
     *
     * @return bool
     */
    protected function isXmlHttpRequest(array $mapping): bool
    {
        return (bool) ($mapping['xmlHttpRequest'] ?? false);
    }

    /**
     * Get mapping priority.
     *
     * @param array $mapping
     *
     * @return int
     */
    protected function getPriority(array $mapping): int
    {
        return \array_key_exists('priority', $mapping) ? (int) $mapping['priority'] : 0;
    }

    /**
     * Get mapping pattern.
     *
     * @param array $mapping
     *
     * @return string|null
     */
    protected function getPattern(array $mapping)
    {
        return \array_key_exists('pattern', $mapping) && \trim($mapping['pattern'], ' /') !== ''
            ? \trim($mapping['pattern'], ' /')
            : null;
    }

    /**
     * Get mapping parameters.
     *
     * @param array $mapping
     *
     * @throws DriverException
     *
     * @return string[]
     */
    protected function getParameters(array $mapping): array
    {
        if (!\array_key_exists('parameters', $mapping)) {
            return [];
        }

        $parameters = $mapping['parameters'];

        \array_map(
            function ($key) {
                if (!\is_string($key)) {
                    throw new DriverException('Parameters keys must be all strings');
                }
            },
            \array_keys($parameters)
        );

        return $parameters;
    }

    /**
     * Get mapping placeholders.
     *
     * @param array $mapping
     *
     * @throws DriverException
     *
     * @return string[]
     */
    protected function getPlaceholders(array $mapping): array
    {
        if (!\array_key_exists('placeholders', $mapping)) {
            return [];
        }

        $placeholders = $mapping['placeholders'];

        \array_map(
            function ($key) {
                if (!\is_string($key)) {
                    throw new DriverException('Placeholder keys must be all strings');
                }
            },
            \array_keys($placeholders)
        );

        return $placeholders;
    }

    /**
     * Get mapping middleware.
     *
     * @param array $mapping
     *
     * @throws DriverException
     *
     * @return array
     */
    protected function getMiddleware(array $mapping): array
    {
        if (!\array_key_exists('middleware', $mapping)) {
            return [];
        }

        $middlewareList = $mapping['middleware'];
        if (!\is_array($middlewareList)) {
            $middlewareList = [$middlewareList];
        }

        foreach ($middlewareList as $middleware) {
            if (!\is_string($middleware)) {
                throw new DriverException(
                    \sprintf('Middleware must be a string or string array. "%s" given', \gettype($middleware))
                );
            }
        }

        return $middlewareList;
    }

    /**
     * Get mapping invokable.
     *
     * @param array $mapping
     *
     * @throws DriverException
     *
     * @return string|array|callable
     */
    protected function getInvokable(array $mapping)
    {
        if (!\array_key_exists('invokable', $mapping)) {
            throw new DriverException('Route invokable definition missing');
        }

        $invokable = $mapping['invokable'];

        if (!\is_string($invokable) && !\is_array($invokable) && !\is_callable($invokable)) {
            throw new DriverException('Route invokable does not seam to be supported by Slim router');
        }

        return $invokable;
    }
}
