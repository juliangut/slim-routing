<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Mapping\Driver;

use Jgut\Mapping\Exception\DriverException;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;

trait FileMappingTrait
{
    /**
     * @return list<RouteMetadata>
     */
    public function getMetadata(): array
    {
        $mappingData = $this->getMappingData();

        return $this->getRoutesMetadata($mappingData);
    }

    /**
     * @return list<GroupMapping|RouteMapping|mixed>
     */
    abstract protected function getMappingData(): array;

    /**
     * @param array<GroupMapping|RouteMapping|mixed> $mappingData
     *
     * @return list<RouteMetadata>
     */
    protected function getRoutesMetadata(array $mappingData, ?GroupMetadata $group = null): array
    {
        $routes = [];

        foreach ($mappingData as $mapping) {
            if (!\is_array($mapping)) {
                continue;
            }

            if (\array_key_exists('routes', $mapping)) {
                /** @var GroupMapping $mapping */
                $groupMetadata = new GroupMetadata();
                if ($group !== null) {
                    $groupMetadata->setParent($group);
                }
                $this->populateGroup($groupMetadata, $mapping);

                /** @var GroupMapping|RouteMapping $routingMapping */
                $routingMapping = $mapping['routes'];

                $routes[] = $this->getRoutesMetadata($routingMapping, $groupMetadata);
            } else {
                /** @var RouteMapping $mapping */
                $routeMetadata = new RouteMetadata($this->getInvokable($mapping));
                if ($group !== null) {
                    $routeMetadata->setGroup($group);
                }
                $this->populateRoute($routeMetadata, $mapping);

                $routes[] = [$routeMetadata];
            }
        }

        return \count($routes) > 0 ? array_values(array_merge(...$routes)) : [];
    }

    /**
     * @param GroupMapping $mapping
     */
    protected function populateGroup(GroupMetadata $group, array $mapping): void
    {
        $this->populatePrefix($group, $mapping);
        $this->populatePattern($group, $mapping);
        $this->populatePlaceholders($group, $mapping);
        $this->populateMiddleware($group, $mapping);
        $this->populateParameters($group, $mapping);
        $this->populateTransformers($group, $mapping);
        $this->populateArguments($group, $mapping);
    }

    /**
     * @param RouteMapping $mapping
     */
    protected function populateRoute(RouteMetadata $route, array $mapping): void
    {
        $name = $this->getName($mapping);
        if ($name !== null) {
            $route->setName($name);
        }
        $this->populatePattern($route, $mapping);
        $this->populateMethods($route, $mapping);
        $this->populateXmlHttpRequest($route, $mapping);
        $this->populatePriority($route, $mapping);
        $this->populatePlaceholders($route, $mapping);
        $this->populateArguments($route, $mapping);
        $this->populateParameters($route, $mapping);
        $this->populateTransformers($route, $mapping);
        $this->populateMiddleware($route, $mapping);
    }

    /**
     * @param RouteMapping $mapping
     *
     * @throws DriverException
     *
     * @return string|array{string, string}|callable(): mixed
     */
    protected function getInvokable(array $mapping)
    {
        if (!\array_key_exists('invokable', $mapping)) {
            throw new DriverException('Route invokable definition missing.');
        }

        $invokable = $mapping['invokable'];

        if (!\is_string($invokable) && !\is_array($invokable) && !\is_callable($invokable)) {
            throw new DriverException('Route invokable does not seem to be supported by Slim router.');
        }

        /** @var string|array{string, string}|callable(): mixed $invokable */
        return $invokable;
    }

    /**
     * @param RouteMapping $mapping
     */
    protected function getName(array $mapping): ?string
    {
        return \array_key_exists('name', $mapping) && trim($mapping['name']) !== ''
            ? trim($mapping['name'])
            : null;
    }

    /**
     * @param GroupMapping $mapping
     */
    protected function populatePrefix(GroupMetadata $metadata, array $mapping): void
    {
        if (\array_key_exists('prefix', $mapping) && trim($mapping['prefix']) !== '') {
            $metadata->setPrefix(trim($mapping['prefix']));
        }
    }

    /**
     * @param GroupMapping|RouteMapping $mapping
     */
    protected function populatePattern(
        GroupMetadata|RouteMetadata $metadata,
        array $mapping,
    ): void {
        if (\array_key_exists('pattern', $mapping) && trim($mapping['pattern'], ' /') !== '') {
            $metadata->setPattern(trim($mapping['pattern'], ' /'));
        }
    }

    /**
     * @param GroupMapping|RouteMapping $mapping
     *
     * @throws DriverException
     */
    protected function populatePlaceholders(
        GroupMetadata|RouteMetadata $metadata,
        array $mapping,
    ): void {
        if (!\array_key_exists('placeholders', $mapping)) {
            return;
        }

        $placeholders = $mapping['placeholders'];

        if (!\is_array($placeholders)) {
            throw new DriverException('Placeholders must be an array.');
        }

        if ($placeholders !== [] && array_is_list($placeholders)) {
            throw new DriverException('Placeholder keys must be all strings.');
        }

        foreach ($placeholders as $placeholder) {
            if (!\is_string($placeholder)) {
                throw new DriverException(
                    sprintf('Placeholders must be strings. "%s" given.', \gettype($placeholder)),
                );
            }
        }

        /** @var array<string, string> $placeholders */
        $metadata->setPlaceholders($placeholders);
    }

    /**
     * @param array<mixed> $mapping
     *
     * @throws DriverException
     */
    protected function populateMethods(RouteMetadata $metadata, array $mapping): void
    {
        $methods = [];

        $mappingMethods = $mapping['methods'] ?? ['GET'];
        if (!\is_array($mappingMethods)) {
            $mappingMethods = [$mappingMethods];
        }

        foreach (array_filter($mappingMethods) as $method) {
            if (!\is_string($method)) {
                throw new DriverException(
                    sprintf('Route methods must be a string or array og string. "%s" given.', \gettype($method)),
                );
            }

            $methods[] = trim($method);
        }

        $methods = array_unique(array_filter($methods, 'strlen'));

        if (\count($methods) === 0) {
            throw new DriverException('Route methods can not be empty.');
        }

        $metadata->setMethods(array_values($methods));
    }

    /**
     * @param RouteMapping $mapping
     *
     * @throws DriverException
     */
    protected function populateXmlHttpRequest(RouteMetadata $metadata, array $mapping): void
    {
        $xmlHttpRequest = $mapping['xmlHttpRequest'] ?? false;

        if (!\is_bool($xmlHttpRequest)) {
            throw new DriverException(
                sprintf('XMLHTTPRequest must be a boolean. "%s" given.', \gettype($xmlHttpRequest)),
            );
        }

        $metadata->setXmlHttpRequest($xmlHttpRequest);
    }

    /**
     * @param RouteMapping $mapping
     *
     * @throws DriverException
     */
    protected function populatePriority(RouteMetadata $metadata, array $mapping): void
    {
        $priority = $mapping['priority'] ?? 0;

        if (!\is_int($priority)) {
            throw new DriverException(
                sprintf('Route priority must be an integer. "%s" given.', \gettype($mapping['priority'] ?? null)),
            );
        }

        $metadata->setPriority($priority);
    }

    /**
     * @param array<mixed> $mapping
     *
     * @throws DriverException
     */
    protected function populateMiddleware(
        GroupMetadata|RouteMetadata $metadata,
        array $mapping,
    ): void {
        if (!\array_key_exists('middlewares', $mapping)) {
            return;
        }

        $middlewareList = $mapping['middlewares'];
        if ($middlewareList !== [] && !\is_array($middlewareList)) {
            throw new DriverException(sprintf(
                'Middlewares must be an array of strings. "%s" given.',
                \is_object($middlewareList) ? $middlewareList::class : \gettype($middlewareList),
            ));
        }

        $metadata->setMiddlewares($middlewareList);
    }

    /**
     * @param array<mixed> $mapping
     *
     * @throws DriverException
     */
    protected function populateArguments(
        GroupMetadata|RouteMetadata $metadata,
        array $mapping,
    ): void {
        if (!\array_key_exists('arguments', $mapping)) {
            return;
        }

        $arguments = $mapping['arguments'];
        if ($arguments !== [] && array_is_list($arguments)) {
            throw new DriverException('Arguments keys must be all strings.');
        }

        $metadata->setArguments($arguments);
    }

    /**
     * @param array<mixed> $mapping
     *
     * @throws DriverException
     */
    protected function populateParameters(
        GroupMetadata|RouteMetadata $metadata,
        array $mapping,
    ): void {
        if (!\array_key_exists('parameters', $mapping)) {
            return;
        }

        $parameters = $mapping['parameters'];
        if ($parameters !== [] && array_is_list($parameters)) {
            throw new DriverException('Parameters keys must be all strings.');
        }

        $metadata->setParameters($parameters);
    }

    /**
     * @param array<mixed> $mapping
     *
     * @throws DriverException
     */
    protected function populateTransformers(
        GroupMetadata|RouteMetadata $metadata,
        array $mapping,
    ): void {
        if (!\array_key_exists('transformers', $mapping)) {
            return;
        }

        $transformers = $mapping['transformers'];
        if ($transformers !== [] && !\is_array($transformers)) {
            throw new DriverException(sprintf(
                'Transformers must be an array of strings. "%s" given.',
                \is_object($transformers) ? $transformers::class : \gettype($transformers),
            ));
        }

        $metadata->setTransformers(array_values($transformers));
    }
}
