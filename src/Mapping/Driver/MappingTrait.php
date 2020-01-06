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
     * @param mixed[]            $mappingData
     * @param GroupMetadata|null $group
     *
     * @return RouteMetadata[]
     */
    protected function getRoutesMetadata(array $mappingData, GroupMetadata $group = null): array
    {
        $routes = [];

        foreach ($mappingData as $mapping) {
            $routes[] = isset($mapping['routes'])
                ? $this->getRoutesMetadata($mapping['routes'], $this->getGroupMetadata($mapping, $group))
                : [$this->getRouteMetadata($mapping, $group)];
        }

        return \count($routes) > 0 ? \array_merge(...$routes) : [];
    }

    /**
     * Get group metadata.
     *
     * @param mixed[]            $mapping
     * @param GroupMetadata|null $parentGroup
     *
     * @return GroupMetadata
     */
    protected function getGroupMetadata(array $mapping, GroupMetadata $parentGroup = null): GroupMetadata
    {
        $group = new GroupMetadata();
        $group->setPlaceholders($this->getPlaceholders($mapping));
        $group->setParameters($this->getParameters($mapping));
        $group->setMiddleware($this->getMiddleware($mapping));

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
     * @param mixed[]            $mapping
     * @param GroupMetadata|null $group
     *
     * @return RouteMetadata
     */
    protected function getRouteMetadata(array $mapping, GroupMetadata $group = null): RouteMetadata
    {
        $route = new RouteMetadata();
        $route->setMethods($this->getMethods($mapping));
        $route->setXmlHttpRequest($this->isXmlHttpRequest($mapping));
        $route->setPriority($this->getPriority($mapping));
        $route->setPlaceholders($this->getPlaceholders($mapping));
        $route->setMiddleware($this->getMiddleware($mapping));
        $route->setInvokable($this->getInvokable($mapping));

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
     * @param mixed[] $mapping
     *
     * @return string|null
     */
    protected function getPrefix(array $mapping): ?string
    {
        return isset($mapping['prefix']) && \trim($mapping['prefix']) !== ''
            ? \trim($mapping['prefix'])
            : null;
    }

    /**
     * Get mapping name.
     *
     * @param mixed[] $mapping
     *
     * @return string|null
     */
    protected function getName(array $mapping): ?string
    {
        return isset($mapping['name']) && \trim($mapping['name']) !== ''
            ? \trim($mapping['name'])
            : null;
    }

    /**
     * Get mapping methods.
     *
     * @param mixed[] $mapping
     *
     * @throws DriverException
     *
     * @return string[]
     */
    protected function getMethods(array $mapping): array
    {
        if (!isset($mapping['methods'])) {
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
     * @param mixed[] $mapping
     *
     * @return string|null
     */
    protected function getTransformer(array $mapping): ?string
    {
        return $mapping['transformer'] ?? null;
    }

    /**
     * Get XmlHttpRequest constraint.
     *
     * @param mixed[] $mapping
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
     * @param mixed[] $mapping
     *
     * @return int
     */
    protected function getPriority(array $mapping): int
    {
        return (int) ($mapping['priority'] ?? 0);
    }

    /**
     * Get mapping pattern.
     *
     * @param mixed[] $mapping
     *
     * @return string|null
     */
    protected function getPattern(array $mapping): ?string
    {
        return isset($mapping['pattern']) && \trim($mapping['pattern'], ' /') !== ''
            ? \trim($mapping['pattern'], ' /')
            : null;
    }

    /**
     * Get mapping parameters.
     *
     * @param mixed[] $mapping
     *
     * @throws DriverException
     *
     * @return string[]
     */
    protected function getParameters(array $mapping): array
    {
        if (!isset($mapping['parameters'])) {
            return [];
        }

        $parameters = $mapping['parameters'];

        if ($parameters !== [] && \array_keys($parameters) === \range(0, \count($parameters) - 1)) {
            throw new DriverException('Parameters keys must be all strings');
        }

        return $parameters;
    }

    /**
     * Get mapping placeholders.
     *
     * @param mixed[] $mapping
     *
     * @throws DriverException
     *
     * @return string[]
     */
    protected function getPlaceholders(array $mapping): array
    {
        if (!isset($mapping['placeholders'])) {
            return [];
        }

        $placeholders = $mapping['placeholders'];

        if ($placeholders !== [] && \array_keys($placeholders) === \range(0, \count($placeholders) - 1)) {
            throw new DriverException('Placeholder keys must be all strings');
        }

        return $placeholders;
    }

    /**
     * Get mapping middleware.
     *
     * @param mixed[] $mapping
     *
     * @throws DriverException
     *
     * @return mixed[]
     */
    protected function getMiddleware(array $mapping): array
    {
        if (!isset($mapping['middleware'])) {
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
     * @param mixed[] $mapping
     *
     * @throws DriverException
     *
     * @return string|mixed[]|callable
     */
    protected function getInvokable(array $mapping)
    {
        if (!isset($mapping['invokable'])) {
            throw new DriverException('Route invokable definition missing');
        }

        $invokable = $mapping['invokable'];

        if (!\is_string($invokable) && !\is_array($invokable) && !\is_callable($invokable)) {
            throw new DriverException('Route invokable does not seam to be supported by Slim router');
        }

        return $invokable;
    }
}
