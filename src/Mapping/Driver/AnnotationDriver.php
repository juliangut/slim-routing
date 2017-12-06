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
use Jgut\Slim\Routing\Mapping\Annotation\Group as GroupAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Route as RouteAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Router as RouterAnnotation;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;

/**
 * Annotation driver.
 */
class AnnotationDriver extends AbstractAnnotationDriver implements DriverInterface
{
    /**
     * {@inheritdoc}
     *
     * @return RouteMetadata[]
     */
    public function getMetadata(): array
    {
        $routes = [];

        $mappingClasses = $this->getMappingClasses();

        $groups = $this->getGroupsMetadata($mappingClasses);

        foreach ($mappingClasses as $class) {
            if ($class->isAbstract()) {
                continue;
            }

            /** @var RouterAnnotation|null $router */
            $router = $this->annotationReader->getClassAnnotation($class, RouterAnnotation::class);
            if ($router !== null) {
                $routes[] = $this->getRoutesMetadata($class, $groups);
            }
        }

        return count($routes) > 0 ? array_merge(...$routes) : [];
    }

    /**
     * Get groups metadata.
     *
     * @param \ReflectionClass[] $mappingClasses
     *
     * @throws \RuntimeException
     *
     * @return GroupMetadata[]
     */
    protected function getGroupsMetadata(array $mappingClasses): array
    {
        $groups = [];

        foreach ($mappingClasses as $class) {
            /** @var GroupAnnotation|null $group */
            $group = $this->annotationReader->getClassAnnotation($class, GroupAnnotation::class);

            if ($group !== null) {
                $groupDataBag = new \stdClass();
                $groupDataBag->parent = $group->getParent();
                $groupDataBag->group = $this->getGroupMetadata($group);

                $groups[$class->getName()] = $groupDataBag;
            }
        }

        /** @var GroupMetadata[] $groups */
        $groups = array_map(
            function (\stdClass $groupDataBag) use ($groups) {
                /** @var GroupMetadata $group */
                $group = $groupDataBag->group;

                $parent = $groupDataBag->parent;
                if ($parent !== null) {
                    if (!array_key_exists($parent, $groups)) {
                        throw new \RuntimeException(sprintf('Parent group %s does not exist', $parent));
                    }

                    $group->setParent($groups[$parent]->group);
                }

                return $group;
            },
            $groups
        );

        return $groups;
    }

    /**
     * Get group metadata.
     *
     * @param GroupAnnotation $annotation
     *
     * @return GroupMetadata
     */
    protected function getGroupMetadata(GroupAnnotation $annotation): GroupMetadata
    {
        $group = (new GroupMetadata())
            ->setPlaceholders($annotation->getPlaceholders())
            ->setMiddleware($annotation->getMiddleware());

        if ($annotation->getPattern() !== null) {
            $group->setPattern($annotation->getPattern());
        }

        if ($annotation->getPrefix() !== null) {
            $group->setPrefix($annotation->getPrefix());
        }

        return $group;
    }

    /**
     * Get processed routes.
     *
     * @param \ReflectionClass $class
     * @param GroupMetadata[]  $groups
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return RouteMetadata[]
     */
    protected function getRoutesMetadata(\ReflectionClass $class, array $groups): array
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
                    throw new \RuntimeException(
                        sprintf('Routes can not be defined in constructor or destructor in class %s', $class->name)
                    );
                }

                $modifiers = array_intersect(
                    ['private', 'protected'],
                    \Reflection::getModifierNames($method->getModifiers())
                );
                if (count($modifiers) !== 0) {
                    throw new \RuntimeException(
                        sprintf('Routes can not be defined in private or protected methods in class %s', $class->name)
                    );
                }

                $routes[] = $this->getRouteMetadata($class, $method, $route, $group);
            }
        }

        if (count($routes) === 0) {
            throw new \RuntimeException(sprintf('Class %s does not define any route', $class->name));
        }

        return $routes;
    }

    /**
     * Get processed route.
     *
     * @param \ReflectionClass   $class
     * @param \ReflectionMethod  $method
     * @param RouteAnnotation    $annotation
     * @param GroupMetadata|null $group
     *
     * @throws \InvalidArgumentException
     *
     * @return RouteMetadata
     */
    protected function getRouteMetadata(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        RouteAnnotation $annotation,
        GroupMetadata $group = null
    ): RouteMetadata {
        $route = (new RouteMetadata())
            ->setPlaceholders($annotation->getPlaceholders())
            ->setMiddleware($annotation->getMiddleware())
            ->setMethods($annotation->getMethods())
            ->setInvokable([$class->name,  $method->name])
            ->setPriority($annotation->getPriority());

        if ($annotation->getPattern() !== null) {
            $route->setPattern($annotation->getPattern());
        }

        if ($annotation->getName() !== null) {
            $route->setName($annotation->getName());
        }

        if ($group !== null) {
            $route->setGroup($group);
        }

        return $route;
    }
}
