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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Jgut\Slim\Routing\Mapping\Annotation\Group as GroupAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Route as RouteAnnotation;
use Jgut\Slim\Routing\Mapping\Annotation\Router as RouterAnnotation;
use Jgut\Slim\Routing\Mapping\Loader\AnnotationLoader;
use Jgut\Slim\Routing\Mapping\RouteMetadata;

/**
 * Annotation mapping driver.
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * Mapping loader.
     *
     * @var AnnotationLoader
     */
    protected $mappingLoader;

    /**
     * Annotation reader.
     *
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * RouteCompiler constructor.
     *
     * @param AnnotationLoader $mappingLoader
     * @param AnnotationReader $annotationReader
     */
    public function __construct(AnnotationLoader $mappingLoader, AnnotationReader $annotationReader)
    {
        $this->mappingLoader = $mappingLoader;
        $this->annotationReader = $annotationReader;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getRoutingMetadata(array $loadingPaths): array
    {
        AnnotationRegistry::registerLoader('class_exists');

        $classes = $this->getReflectionClasses($loadingPaths);
        $groupAnnotations = $this->getAnnotationGroups($classes);

        $routes = [];
        foreach ($classes as $class) {
            if ($class->isAbstract()) {
                continue;
            }

            /* @var RouterAnnotation $routerAnnotation */
            $routerAnnotation = $this->annotationReader->getClassAnnotation($class, RouterAnnotation::class);

            if ($routerAnnotation) {
                $routes[] = $this->getRoutesMetadata($class, $groupAnnotations);
            }
        }

        return count($routes) ? array_merge(...$routes) : [];
    }

    /**
     * Get group annotations.
     *
     * @param \ReflectionClass[] $classes
     *
     * @return GroupAnnotation[]
     */
    final protected function getAnnotationGroups(array $classes): array
    {
        $groups = [];

        foreach ($classes as $class) {
            /* @var GroupAnnotation $groupAnnotation */
            $groupAnnotation = $this->annotationReader->getClassAnnotation($class, GroupAnnotation::class);

            if ($groupAnnotation && $groupAnnotation->getName() !== '') {
                $groups[$groupAnnotation->getName()] = $groupAnnotation;
            }
        }

        return $groups;
    }

    /**
     * Get processed routes.
     *
     * @param \ReflectionClass  $class
     * @param GroupAnnotation[] $groupAnnotations
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return RouteMetadata[]
     */
    protected function getRoutesMetadata(\ReflectionClass $class, array $groupAnnotations): array
    {
        $routes = [];

        /* @var GroupAnnotation $groupAnnotation */
        $groupAnnotation = $this->annotationReader->getClassAnnotation($class, GroupAnnotation::class);

        foreach ($class->getMethods() as $method) {
            /* @var RouteAnnotation $routeAnnotation */
            $routeAnnotation = $this->annotationReader->getMethodAnnotation($method, RouteAnnotation::class);

            if ($routeAnnotation) {
                if ($method->isConstructor() || $method->isDestructor()) {
                    throw new \RuntimeException(
                        sprintf('Routes can not be defined in constructor or destructor in class %s', $class->name)
                    );
                }

                $modifiers = array_intersect(
                    ['private', 'protected'],
                    \Reflection::getModifierNames($method->getModifiers())
                );
                if (count($modifiers)) {
                    throw new \RuntimeException(
                        sprintf('Routes can not be defined in private or protected methods in class %s', $class->name)
                    );
                }

                $routes[] = $this->getRouteMetadata(
                    $class,
                    $method,
                    $routeAnnotation,
                    $groupAnnotation,
                    $groupAnnotations
                );
            }
        }

        if (!count($routes)) {
            throw new \RuntimeException(sprintf('Class %s does not define any route', $class->name));
        }

        return $routes;
    }

    /**
     * Get processed route.
     *
     * @param \ReflectionClass     $class
     * @param \ReflectionMethod    $method
     * @param RouteAnnotation      $routeAnnotation
     * @param GroupAnnotation|null $groupAnnotation
     * @param GroupAnnotation[]    $groupAnnotations
     *
     * @throws \InvalidArgumentException
     *
     * @return RouteMetadata
     */
    protected function getRouteMetadata(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        RouteAnnotation $routeAnnotation,
        GroupAnnotation $groupAnnotation = null,
        array $groupAnnotations = []
    ): RouteMetadata {
        $groupChain = $this->getGroupChain($class, $groupAnnotation, $groupAnnotations);

        return (new RouteMetadata())
            ->setPrefixes($this->getRoutePrefixList($groupChain))
            ->setName($routeAnnotation->getName())
            ->setPriority($routeAnnotation->getPriority())
            ->setMethods($routeAnnotation->getMethods())
            ->setPattern($this->getRoutePattern($routeAnnotation, $groupChain))
            ->setPlaceholders($this->getRoutePlaceholders($routeAnnotation, $groupChain))
            ->setMiddleware($this->getRouteMiddleware($routeAnnotation, $groupChain))
            ->setInvokable([$class->name,  $method->name]);
    }

    /**
     * Get group annotations chain.
     *
     * @param \ReflectionClass     $class
     * @param GroupAnnotation|null $groupAnnotation
     * @param GroupAnnotation[]    $groupAnnotations
     *
     * @throws \RuntimeException
     *
     * @return GroupAnnotation[]
     */
    protected function getGroupChain(
        \ReflectionClass $class,
        GroupAnnotation $groupAnnotation = null,
        array $groupAnnotations = []
    ): array {
        if ($groupAnnotation === null) {
            return [];
        }

        $groupChain = [
            $groupAnnotation->getName() => $groupAnnotation,
        ];

        $group = $groupAnnotation;
        while ($group->getParent() !== '') {
            $parentGroup = $group->getParent();

            if (!array_key_exists($parentGroup, $groupAnnotations)) {
                throw new \RuntimeException(
                    sprintf(
                        'Referenced group "%s" on class %s is not defined',
                        $parentGroup,
                        $class->name
                    )
                );
            }

            if (array_key_exists($parentGroup, $groupChain)) {
                throw new \RuntimeException(
                    sprintf(
                        'Circular reference detected with group "%s" on class %s',
                        $parentGroup,
                        $class->name
                    )
                );
            }

            $groupChain[$parentGroup] = $group = $groupAnnotations[$parentGroup];
        }

        return array_reverse(array_values($groupChain));
    }

    /**
     * Get route prefix list.
     *
     * @param GroupAnnotation[] $groupChain
     *
     * @return string[]
     */
    protected function getRoutePrefixList(array $groupChain): array
    {
        return array_filter(array_map(
            function (GroupAnnotation $groupAnnotation) {
                return $groupAnnotation->getPrefix();
            },
            $groupChain
        ));
    }

    /**
     * Get compound path.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupChain
     *
     * @return string
     */
    protected function getRoutePattern(
        RouteAnnotation $routeAnnotation,
        array $groupChain
    ): string {
        $patterns = array_map(
            function (GroupAnnotation $groupAnnotation) {
                return $groupAnnotation->getPattern();
            },
            $groupChain
        );
        $patterns[] = $routeAnnotation->getPattern();

        return implode('', array_filter($patterns));
    }

    /**
     * Get compound placeholders.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupChain
     *
     * @return array
     */
    protected function getRoutePlaceholders(
        RouteAnnotation $routeAnnotation,
        array $groupChain
    ): array {
        $placeholders = array_map(
            function (GroupAnnotation $groupAnnotation) {
                return $groupAnnotation->getPlaceholders();
            },
            $groupChain
        );
        $placeholders[] = $routeAnnotation->getPlaceholders();

        return array_filter(array_merge(...$placeholders));
    }

    /**
     * Get compound middleware.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupChain
     *
     * @return array
     */
    protected function getRouteMiddleware(
        RouteAnnotation $routeAnnotation,
        array $groupChain
    ): array {
        $middleware = array_map(
            function (GroupAnnotation $groupAnnotation) {
                return $groupAnnotation->getMiddleware();
            },
            array_reverse($groupChain)
        );
        array_unshift($middleware, $routeAnnotation->getMiddleware());

        return array_filter(array_merge(...$middleware));
    }

    /**
     * Get reflection classes.
     *
     * @param array $loadingPaths
     *
     * @throws \RuntimeException
     *
     * @return \ReflectionClass[]
     */
    final protected function getReflectionClasses(array $loadingPaths): array
    {
        $classes = [];

        foreach ($this->mappingLoader->getMappingData($loadingPaths) as $routingClass) {
            $classes[] = new \ReflectionClass($routingClass);
        }

        return $classes;
    }
}
