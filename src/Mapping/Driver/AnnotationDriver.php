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

use Jgut\Mapping\Driver\AbstractAnnotationDriver;
use Jgut\Mapping\Exception\DriverException;
use Jgut\Slim\Routing\Mapping\Annotation\Group as GroupAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Route as RouteAnnotation;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Reflection;
use ReflectionClass;
use ReflectionMethod;

final class AnnotationDriver extends AbstractAnnotationDriver
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

            $routes[] = $this->getRoutesMetadata($class, $groups);
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

        /** @var GroupAnnotation|null $groupAnnotation */
        $groupAnnotation = $this->annotationReader->getClassAnnotation($class, GroupAnnotation::class);

        if ($groupAnnotation !== null) {
            $group = $groups[$class->getName()];
        }

        foreach ($class->getMethods() as $method) {
            /** @var RouteAnnotation|null $route */
            $route = $this->annotationReader->getMethodAnnotation($method, RouteAnnotation::class);
            if ($route !== null) {
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
                    throw new DriverException(sprintf(
                        'Routes can not be defined in private or protected methods in class "%s".',
                        $class->name,
                    ));
                }

                $routeMetadata = new RouteMetadata($class->name . ':' . $method->name, $route->getName());
                if ($group !== null) {
                    $routeMetadata->setGroup($group);
                }
                $this->populateRoute($routeMetadata, $method, $route);

                $routes[] = $routeMetadata;
            }
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
            /** @var GroupAnnotation|null $group */
            $group = $this->annotationReader->getClassAnnotation($class, GroupAnnotation::class);
            if ($group !== null) {
                $groupMetadata = new GroupMetadata();
                $this->populateGroup($groupMetadata, $group);

                $groups[$class->getName()] = [
                    'group' => $groupMetadata,
                    'parent' => $group->getParent(),
                ];
            }
        }

        return array_map(
            /** @var array{group: GroupMetadata, parent: ?string} $groupDataBag */
            static function (array $groupDataBag) use ($groups): GroupMetadata {
                $group = $groupDataBag['group'];

                $parent = $groupDataBag['parent'];
                if ($parent !== null) {
                    if (!\array_key_exists($parent, $groups)) {
                        throw new DriverException(sprintf('Parent group "%s" does not exist.', $parent));
                    }

                    $group->setParent($groups[$parent]['group']);
                }

                return $group;
            },
            $groups,
        );
    }

    protected function populateGroup(GroupMetadata $group, GroupAnnotation $annotation): void
    {
        $this->populatePrefix($group, $annotation);
        $this->populatePattern($group, $annotation);
        $group->setPlaceholders($annotation->getPlaceholders());
        $group->setParameters($annotation->getParameters());
        $group->setMiddleware($annotation->getMiddleware());
        $group->setArguments($annotation->getArguments());
    }

    /**
     * @throws DriverException
     */
    protected function populateRoute(
        RouteMetadata $route,
        ReflectionMethod $method,
        RouteAnnotation $annotation,
    ): void {
        $this->populatePattern($route, $annotation);
        $route->setPlaceholders($annotation->getPlaceholders());
        $route->setParameters($annotation->getParameters());
        $route->setMethods($annotation->getMethods());
        $route->setXmlHttpRequest($annotation->isXmlHttpRequest());
        $route->setMiddleware($annotation->getMiddleware());
        $route->setArguments($annotation->getArguments());
        $route->setPriority($annotation->getPriority());
        $this->populateTransformer($route, $annotation, $method);
    }

    protected function populatePrefix(GroupMetadata $metadata, GroupAnnotation $annotation): void
    {
        $prefix = $annotation->getPrefix();
        if ($prefix !== null) {
            $metadata->setPrefix($prefix);
        }
    }

    /**
     * @param GroupMetadata|RouteMetadata     $metadata
     * @param GroupAnnotation|RouteAnnotation $annotation
     */
    protected function populatePattern($metadata, $annotation): void
    {
        $pattern = $annotation->getPattern();
        if ($pattern !== null) {
            $metadata->setPattern($pattern);
        }
    }

    protected function populateTransformer(
        RouteMetadata $route,
        RouteAnnotation $annotation,
        ReflectionMethod $method,
    ): void {
        if ($annotation->getTransformers() !== null) {
            $route->setTransformers($annotation->getTransformers())
                ->setParameters($this->getRouteParameters($method, $annotation));
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getRouteParameters(ReflectionMethod $method, RouteAnnotation $annotation): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type !== null) {
                $parameters[$parameter->getName()] = $type->getName();
            }
        }

        return array_merge($parameters, $annotation->getParameters());
    }
}
