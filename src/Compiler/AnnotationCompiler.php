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

namespace Jgut\Slim\Routing\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Jgut\Slim\Routing\Annotation\Group as GroupAnnotation;
use Jgut\Slim\Routing\Annotation\Route as RouteAnnotation;
use Jgut\Slim\Routing\Annotation\Router as RouterAnnotation;
use Jgut\Slim\Routing\Route;

/**
 * Routing annotation compiler.
 */
class AnnotationCompiler extends AbstractCompiler
{
    /**
     * {@inheritdoc}
     */
    public function getRoutes(array $routingSources): array
    {
        AnnotationRegistry::registerLoader('class_exists');

        $annotationReader = new AnnotationReader();

        $classes = $this->getClasses($routingSources);
        $groups = $this->getNamedGroups($classes, $annotationReader);

        $routes = [];
        foreach ($classes as $class) {
            /* @var RouterAnnotation $routerAnnotation */
            $routerAnnotation = $annotationReader->getClassAnnotation($class, RouterAnnotation::class);

            if ($routerAnnotation !== null) {
                $routes = array_merge(
                    $routes,
                    $this->getClassRoutes($class, $annotationReader, $groups)
                );
            }
        }

        return $routes;
    }

    /**
     * Get processed routes.
     *
     * @param \ReflectionClass  $class
     * @param AnnotationReader  $annotationReader
     * @param GroupAnnotation[] $groupAnnotations
     *
     * @throws \RuntimeException
     *
     * @return Route[]
     */
    protected function getClassRoutes(
        \ReflectionClass $class,
        AnnotationReader $annotationReader,
        array $groupAnnotations
    ): array {
        $routes = [];

        /* @var GroupAnnotation $groupAnnotation */
        $groupAnnotation = $annotationReader->getClassAnnotation($class, GroupAnnotation::class);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            /* @var RouteAnnotation $routeAnnotation */
            $routeAnnotation = $annotationReader->getMethodAnnotation($method, RouteAnnotation::class);

            if ($routeAnnotation !== null) {
                $routes[] = $this->getCompiledRoute(
                    $class,
                    $method,
                    $routeAnnotation,
                    $groupAnnotations,
                    $groupAnnotation
                );
            }
        }

        if (!count($routes)) {
            throw new \RuntimeException(sprintf('Class %s does not define any route', $class->getName()));
        }

        return $routes;
    }

    /**
     * Get processed route.
     *
     * @param \ReflectionClass     $class
     * @param \ReflectionMethod    $method
     * @param RouteAnnotation      $routeAnnotation
     * @param GroupAnnotation[]    $groupAnnotations
     * @param GroupAnnotation|null $groupAnnotation
     *
     * @throws \RuntimeException
     *
     * @return Route
     */
    protected function getCompiledRoute(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        RouteAnnotation $routeAnnotation,
        array $groupAnnotations,
        GroupAnnotation $groupAnnotation = null
    ): Route {
        if ($groupAnnotation) {
            $groupAnnotations = $this->getGroupChain($class, $groupAnnotation, $groupAnnotations);

            $pattern = $this->getCompoundPattern($routeAnnotation, $groupAnnotations);
            $placeholders = $this->getCompoundPlaceholders($routeAnnotation, $groupAnnotations);
            $middleware = $this->getCompoundMiddleware($routeAnnotation, $groupAnnotations);
        } else {
            $pattern = $routeAnnotation->getPattern();
            $placeholders = $routeAnnotation->getPlaceholders();
            $middleware = $routeAnnotation->getMiddleware();
        }

        $this->checkPath($pattern, $placeholders);

        return (new Route())
            ->setName($routeAnnotation->getName())
            ->setPriority($routeAnnotation->getPriority())
            ->setMethods($routeAnnotation->getMethods())
            ->setPattern($pattern)
            ->setPlaceholders($placeholders)
            ->setMiddleware($middleware)
            ->setInvokable([$class->getName(), $method->getName()]);
    }

    /**
     * Get group chain.
     *
     * @param \ReflectionClass  $class
     * @param GroupAnnotation   $groupAnnotation
     * @param GroupAnnotation[] $groupAnnotations
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return GroupAnnotation[]
     */
    protected function getGroupChain(
        \ReflectionClass $class,
        GroupAnnotation $groupAnnotation,
        array $groupAnnotations
    ): array {
        $groupChain = [
            $groupAnnotation->getName() => $groupAnnotation,
        ];

        $group = $groupAnnotation;
        while ($group->getGroup() !== '') {
            $referencedGroup = $group->getGroup();

            if (!array_key_exists($referencedGroup, $groupAnnotations)) {
                throw new \RuntimeException(
                    sprintf(
                        'Referenced group "%s" on class %s is not defined',
                        $referencedGroup,
                        $class->getName()
                    )
                );
            }

            if (array_key_exists($referencedGroup, $groupChain)) {
                throw new \RuntimeException(
                    sprintf(
                        'Circular reference detected with group "%s" on class %s',
                        $referencedGroup,
                        $class->getName()
                    )
                );
            }

            $groupChain[$referencedGroup] = $groupAnnotations[$referencedGroup];

            $group = $groupAnnotations[$referencedGroup];
        }

        return array_reverse(array_values($groupChain));
    }

    /**
     * Get compound path.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupAnnotations
     *
     * @return string
     */
    protected function getCompoundPattern(
        RouteAnnotation $routeAnnotation,
        array $groupAnnotations
    ): string {
        $patterns = array_map(
            function (GroupAnnotation $groupAnnotation) {
                return $groupAnnotation->getPattern();
            },
            $groupAnnotations
        );
        $patterns[] = $routeAnnotation->getPattern();

        return preg_replace('!//+!', '/', implode('', $patterns));
    }

    /**
     * Get compound placeholders.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupAnnotations
     *
     * @return array
     */
    protected function getCompoundPlaceholders(
        RouteAnnotation $routeAnnotation,
        array $groupAnnotations
    ): array {
        $placeholders = array_map(
            function (GroupAnnotation $groupAnnotation) {
                return $groupAnnotation->getPlaceholders();
            },
            $groupAnnotations
        );
        $placeholders[] = $routeAnnotation->getPlaceholders();

        return array_merge(...$placeholders);
    }

    /**
     * Get compound middleware.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupAnnotations
     *
     * @return array
     */
    protected function getCompoundMiddleware(
        RouteAnnotation $routeAnnotation,
        array $groupAnnotations
    ): array {
        $middleware = array_map(
            function (GroupAnnotation $groupAnnotation) {
                return $groupAnnotation->getMiddleware();
            },
            array_reverse($groupAnnotations)
        );
        array_unshift($middleware, $routeAnnotation->getMiddleware());

        return array_merge(...$middleware);
    }

    /**
     * Get reflection classes.
     *
     * @param array $routingSources
     *
     * @return \ReflectionClass[]
     */
    protected function getClasses(array $routingSources): array
    {
        $classes = [];

        foreach ($routingSources as $routingClass) {
            $class = new \ReflectionClass($routingClass);

            if (!$class->isAbstract()) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Get group annotations.
     *
     * @param \ReflectionClass[] $classes
     * @param AnnotationReader   $annotationReader
     *
     * @return GroupAnnotation[]
     */
    protected function getNamedGroups(array $classes, AnnotationReader $annotationReader): array
    {
        $groups = [];

        foreach ($classes as $class) {
            /* @var GroupAnnotation $groupAnnotation */
            $groupAnnotation = $annotationReader->getClassAnnotation($class, GroupAnnotation::class);

            if ($groupAnnotation !== null && $groupAnnotation->getName() !== '') {
                $groups[$groupAnnotation->getName()] = $groupAnnotation;
            }
        }

        return $groups;
    }
}
