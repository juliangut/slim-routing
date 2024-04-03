<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
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

        return \count($routes) > 0 ? array_merge(...$routes) : [];
    }

    /**
     * @param ReflectionClass<object>      $class
     * @param array<string, GroupMetadata> $groups
     *
     * @throws DriverException
     *
     * @return list<RouteMetadata>
     */
    private function getRoutesMetadata(ReflectionClass $class, array $groups): array
    {
        $routes = [];

        $group = null;

        /** @var GroupAnnotation|null $groupAnnotation */
        $groupAnnotation = $this->annotationReader->getClassAnnotation($class, GroupAnnotation::class);

        if ($groupAnnotation !== null) {
            $group = $groups[$class->getName()];
        }

        foreach ($class->getMethods() as $method) {
            $methodAnnotations = $this->annotationReader->getMethodAnnotations($method);

            foreach ($methodAnnotations as $methodAnnotation) {
                if (!$methodAnnotation instanceof RouteAnnotation) {
                    continue;
                }

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

                $routeMetadata = new RouteMetadata($class->name . ':' . $method->name);
                if ($group !== null) {
                    $routeMetadata->setGroup($group);
                }
                $this->populateRoute($routeMetadata, $method, $methodAnnotation);

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
     * @return array<string, GroupMetadata>
     */
    private function getGroups(array $mappingClasses): array
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

    private function populateGroup(GroupMetadata $group, GroupAnnotation $annotation): void
    {
        $this->populatePrefix($group, $annotation);
        $this->populatePattern($group, $annotation);
        $group->setPlaceholders($annotation->getPlaceholders());
        $group->setArguments($annotation->getArguments());
        $this->populateTransformer($group, $annotation);
        $group->setMiddlewares($annotation->getMiddlewares());
    }

    /**
     * @throws DriverException
     */
    private function populateRoute(
        RouteMetadata $route,
        ReflectionMethod $method,
        RouteAnnotation $annotation,
    ): void {
        $name = $annotation->getName();
        if ($name !== null) {
            $route->setName($name);
        }
        $this->populatePattern($route, $annotation);
        $route->setMethods($annotation->getMethods());
        $route->setXmlHttpRequest($annotation->isXmlHttpRequest());
        $route->setPriority($annotation->getPriority());
        $route->setPlaceholders($annotation->getPlaceholders());
        $route->setArguments($annotation->getArguments());
        $this->populateTransformer($route, $annotation, $method);
        $route->setMiddlewares($annotation->getMiddlewares());
    }

    private function populatePrefix(GroupMetadata $metadata, GroupAnnotation $annotation): void
    {
        $prefix = $annotation->getPrefix();
        if ($prefix !== null) {
            $metadata->setPrefix($prefix);
        }
    }

    private function populatePattern(
        GroupMetadata|RouteMetadata $metadata,
        GroupAnnotation|RouteAnnotation $annotation,
    ): void {
        $pattern = $annotation->getPattern();
        if ($pattern !== null) {
            $metadata->setPattern($pattern);
        }
    }

    private function populateTransformer(
        GroupMetadata|RouteMetadata $metadata,
        GroupAnnotation|RouteAnnotation $annotation,
        ?ReflectionMethod $method = null,
    ): void {
        $metadata->setParameters($this->getTransformerParameters($annotation, $method))
            ->setTransformers($annotation->getTransformers());
    }

    /**
     * @param GroupAnnotation|RouteAnnotation $annotation
     *
     * @return array<string, string>
     */
    private function getTransformerParameters($annotation, ?ReflectionMethod $reflection): array
    {
        $parameters = [];

        foreach ($reflection?->getParameters() ?? [] as $parameter) {
            $type = $parameter->getType();

            if ($type !== null) {
                $parameters[$parameter->getName()] = $type->getName();
            }
        }

        return array_merge($parameters, $annotation->getParameters());
    }
}
