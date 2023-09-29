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

use Jgut\Mapping\Driver\AbstractClassDriver;
use Jgut\Mapping\Exception\DriverException;
use Jgut\Slim\Routing\Mapping\Attribute\Group as GroupAttribute;
use Jgut\Slim\Routing\Mapping\Attribute\Middleware as MiddlewareAttribute;
use Jgut\Slim\Routing\Mapping\Attribute\Route as RouteAttribute;
use Jgut\Slim\Routing\Mapping\Attribute\Router as RouterAttribute;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Reflection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use stdClass;

final class AttributeDriver extends AbstractClassDriver
{
    /**
     * @return list<RouteMetadata>
     */
    public function getMetadata(): array
    {
        $routes = [];

        $mappingClasses = $this->getMappingClasses();

        $groups = $this->getGroups($mappingClasses);

        foreach ($mappingClasses as $class) {
            if ($class->isAbstract()) {
                continue;
            }

            $routerAttributes = $class->getAttributes(RouterAttribute::class);
            if (\count($routerAttributes) !== 0) {
                $routes[] = $this->getRoutesMetadata($class, $groups);
            }
        }

        return \count($routes) > 0 ? array_values(array_merge(...$routes)) : [];
    }

    /**
     * @param ReflectionClass<object>                    $class
     * @param array<class-string<object>, GroupMetadata> $groups
     *
     * @throws DriverException
     *
     * @return list<RouteMetadata>
     */
    protected function getRoutesMetadata(ReflectionClass $class, array $groups): array
    {
        $routes = [];

        $group = null;

        $groupAttributes = $class->getAttributes(GroupAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
        if (\count($groupAttributes) !== 0) {
            $group = $groups[$class->getName()];
        }

        foreach ($class->getMethods() as $method) {
            $routeAttributes = $method->getAttributes(RouteAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
            foreach ($routeAttributes as $routeAttribute) {
                if ($method->isConstructor() || $method->isDestructor()) {
                    throw new DriverException(
                        sprintf('Routes can not be defined in constructor or destructor in class "%s".', $class->name),
                    );
                }

                $modifiers = array_intersect(
                    ['private', 'protected'],
                    Reflection::getModifierNames($method->getModifiers()),
                );
                if (\count($modifiers) !== 0) {
                    throw new DriverException(
                        sprintf(
                            'Routes can not be defined in private or protected methods in class "%s".',
                            $class->name,
                        ),
                    );
                }

                /** @var RouteAttribute $route */
                $route = $routeAttribute->newInstance();

                $routeMetadata = new RouteMetadata($class->name . ':' . $method->name, $route->getName());
                if ($group !== null) {
                    $routeMetadata->setGroup($group);
                }
                $this->populateRoute($routeMetadata, $method, $route);

                $routes[] = $routeMetadata;
            }
        }

        if (\count($routes) === 0) {
            throw new DriverException(sprintf('Class "%s" does not define any route.', $class->name));
        }

        return $routes;
    }

    /**
     * @param list<ReflectionClass<object>> $mappingClasses
     *
     * @throws DriverException
     *
     * @return array<class-string<object>, GroupMetadata>
     */
    protected function getGroups(array $mappingClasses): array
    {
        $groups = [];

        foreach ($mappingClasses as $class) {
            $groupAttributes = $class->getAttributes(GroupAttribute::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($groupAttributes as $attribute) {
                /** @var GroupAttribute $group */
                $group = $attribute->newInstance();

                $groupMetadata = new GroupMetadata();
                $this->populateGroup($groupMetadata, $class, $group);

                $groupDataBag = new stdClass();
                $groupDataBag->parent = $group->getParent();
                $groupDataBag->group = $groupMetadata;

                $groups[$class->getName()] = $groupDataBag;
            }
        }

        return array_map(
            static function (stdClass $groupDataBag) use ($groups): GroupMetadata {
                $group = $groupDataBag->group;

                $parent = $groupDataBag->parent;
                if ($parent !== null) {
                    if (!\array_key_exists($parent, $groups)) {
                        throw new DriverException(sprintf('Parent group "%s" does not exist.', $parent));
                    }

                    $group->setParent($groups[$parent]->group);
                }

                return $group;
            },
            $groups,
        );
    }

    /**
     * @param ReflectionClass<object> $class
     */
    protected function populateGroup(
        GroupMetadata $group,
        ReflectionClass $class,
        GroupAttribute $attribute,
    ): void {
        $this->populatePrefix($group, $attribute);
        $this->populatePattern($group, $attribute);
        $group->setPlaceholders($attribute->getPlaceholders());
        $group->setParameters($attribute->getParameters());
        $this->populateMiddleware($group, $class);
        $group->setArguments($attribute->getArguments());
    }

    protected function populateRoute(
        RouteMetadata $route,
        ReflectionMethod $method,
        RouteAttribute $attribute,
    ): void {
        $this->populatePattern($route, $attribute);
        $route->setPlaceholders($attribute->getPlaceholders());
        $route->setParameters($attribute->getParameters());
        $route->setMethods($attribute->getMethods());
        $route->setXmlHttpRequest($attribute->isXmlHttpRequest());
        $this->populateMiddleware($route, $method);
        $route->setArguments($attribute->getArguments());
        $route->setPriority($attribute->getPriority());
        $this->populateTransformer($route, $attribute, $method);
    }

    protected function populatePrefix(GroupMetadata $metadata, GroupAttribute $attribute): void
    {
        $prefix = $attribute->getPrefix();
        if ($prefix !== null) {
            $metadata->setPrefix($prefix);
        }
    }

    /**
     * @param GroupMetadata|RouteMetadata   $metadata
     * @param GroupAttribute|RouteAttribute $attribute
     */
    protected function populatePattern($metadata, $attribute): void
    {
        $pattern = $attribute->getPattern();
        if ($pattern !== null) {
            $metadata->setPattern($pattern);
        }
    }

    /**
     * @param GroupMetadata|RouteMetadata              $metadata
     * @param ReflectionClass<object>|ReflectionMethod $reflection
     */
    protected function populateMiddleware($metadata, $reflection): void
    {
        $middlewareList = [];
        $middlewareAttributes = $reflection->getAttributes(
            MiddlewareAttribute::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );
        foreach ($middlewareAttributes as $middlewareAttribute) {
            /** @var MiddlewareAttribute $middleware */
            $middleware = $middlewareAttribute->newInstance();

            $middlewareList[] = $middleware->getMiddleware();
        }
        if (\count($middlewareList) !== 0) {
            $metadata->setMiddleware($middlewareList);
        }
    }

    protected function populateTransformer(
        RouteMetadata $route,
        RouteAttribute $attribute,
        ReflectionMethod $method,
    ): void {
        if ($attribute->getTransformer() !== null) {
            $route->setTransformer($attribute->getTransformer())
                ->setParameters($this->getRouteParameters($method, $attribute));
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getRouteParameters(ReflectionMethod $method, RouteAttribute $attribute): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            /** @var ReflectionNamedType|null $type */
            $type = $parameter->getType();
            if ($type !== null) {
                $parameters[$parameter->getName()] = $type->getName();
            }
        }

        return array_merge($parameters, $attribute->getParameters());
    }
}
