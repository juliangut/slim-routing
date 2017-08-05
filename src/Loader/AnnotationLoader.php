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

namespace Jgut\Slim\Routing\Loader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Jgut\Slim\Routing\Annotation\Group as GroupAnnotation;
use Jgut\Slim\Routing\Annotation\Route as RouteAnnotation;
use Jgut\Slim\Routing\Annotation\Router as RouterAnnotation;
use Jgut\Slim\Routing\Route;

/**
 * Classes routing loader.
 */
class AnnotationLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function load(array $loadingPaths): array
    {
        $routingSources = $this->getRoutingSources($loadingPaths);

        AnnotationRegistry::registerLoader('class_exists');

        $annotationReader = new AnnotationReader();

        $classes = $this->getClasses($routingSources);
        $groups = $this->getNamedGroups($classes, $annotationReader);

        $loadedData = [];
        foreach ($classes as $class) {
            /* @var RouterAnnotation $routerAnnotation */
            $routerAnnotation = $annotationReader->getClassAnnotation($class, RouterAnnotation::class);

            if ($routerAnnotation) {
                $loadedData[] = $this->getClassRoutes($class, $annotationReader, $groups);
            }
        }

        return count($loadedData) ? array_merge(...$loadedData) : [];
    }

    /**
     * Get routing sources.
     *
     * @param array $loadingPaths
     *
     * @return string[]
     */
    protected function getRoutingSources(array $loadingPaths): array
    {
        $routingFiles = [];

        foreach ($loadingPaths as $path) {
            if (is_dir($path)) {
                $routingFiles[] = $this->loadSourcesFromDirectory($path);
            } elseif (is_file($path)) {
                $routingFiles[] = [$this->loadSourceFromFile($path)];
            } else {
                throw new \RuntimeException(sprintf('Path "%s" does not exist', $path));
            }
        }

        $routingFiles = count($routingFiles) ? array_merge(...$routingFiles) : [];

        return array_filter(array_unique($routingFiles));
    }

    /**
     * Load files from directory.
     *
     * @param string $directory
     *
     * @return string[]
     */
    protected function loadSourcesFromDirectory(string $directory): array
    {
        $routingFiles = [];

        foreach (glob($directory . '/{**/*,*}.php', GLOB_BRACE | GLOB_ERR) as $file) {
            if (is_file($file)) {
                $routingFiles[] = $this->loadSourceFromFile($file);
            }
        }

        return $routingFiles;
    }

    /**
     * Load fully qualified class name from file.
     *
     * @param string $file
     *
     * @return string
     *
     * @SuppressWarnings(PMD.CyclomaticComplexity)
     * @SuppressWarnings(PMD.NPathComplexity)
     */
    protected function loadSourceFromFile(string $file): string
    {
        $tokens = token_get_all(file_get_contents($file));
        $hasClass = false;
        $class = null;
        $hasNamespace = false;
        $namespace = '';

        for ($i = 0, $length = count($tokens); $i < $length; $i++) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if ($hasClass && $token[0] === T_STRING) {
                $class = $namespace . '\\' . $token[1];

                break;
            }

            if ($hasNamespace && $token[0] === T_STRING) {
                $namespace = '';

                do {
                    $namespace .= $token[1];

                    $token = $tokens[++$i];
                } while ($i < $length && is_array($token) && in_array($token[0], [T_NS_SEPARATOR, T_STRING]));

                $hasNamespace = false;
            }

            if ($token[0] == T_CLASS) {
                $hasClass = true;
            }
            if ($token[0] === T_NAMESPACE) {
                $hasNamespace = true;
            }
        }

        return $class ?: '';
    }

    /**
     * Get reflection classes.
     *
     * @param array $routingSources
     *
     * @return \ReflectionClass[]
     */
    final protected function getClasses(array $routingSources): array
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
    final protected function getNamedGroups(array $classes, AnnotationReader $annotationReader): array
    {
        $groups = [];

        foreach ($classes as $class) {
            /* @var GroupAnnotation $groupAnnotation */
            $groupAnnotation = $annotationReader->getClassAnnotation($class, GroupAnnotation::class);

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
     * @param AnnotationReader  $annotationReader
     * @param GroupAnnotation[] $definedGroups
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return Route[]
     */
    protected function getClassRoutes(
        \ReflectionClass $class,
        AnnotationReader $annotationReader,
        array $definedGroups
    ): array {
        $routes = [];

        /* @var GroupAnnotation $groupAnnotation */
        $groupAnnotation = $annotationReader->getClassAnnotation($class, GroupAnnotation::class);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            /* @var RouteAnnotation $routeAnnotation */
            $routeAnnotation = $annotationReader->getMethodAnnotation($method, RouteAnnotation::class);

            if ($routeAnnotation) {
                if ($method->isConstructor()) {
                    throw new \RuntimeException(
                        sprintf('Routes can not be defined in constructor in %s class', $class->name)
                    );
                }

                $routes[] = $this->getCompiledRoute(
                    $class,
                    $method,
                    $routeAnnotation,
                    $definedGroups,
                    $groupAnnotation
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
     * @param GroupAnnotation[]    $definedGroups
     * @param GroupAnnotation|null $groupAnnotation
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function getCompiledRoute(
        \ReflectionClass $class,
        \ReflectionMethod $method,
        RouteAnnotation $routeAnnotation,
        array $definedGroups,
        GroupAnnotation $groupAnnotation = null
    ): array {
        if ($groupAnnotation) {
            $definedGroups = $this->getGroupChain($class, $groupAnnotation, $definedGroups);

            $pattern = $this->getCompoundPattern($routeAnnotation, $definedGroups);
            $placeholders = $this->getCompoundPlaceholders($routeAnnotation, $definedGroups);
            $middleware = $this->getCompoundMiddleware($routeAnnotation, $definedGroups);
        } else {
            $pattern = $routeAnnotation->getPattern();
            $placeholders = $routeAnnotation->getPlaceholders();
            $middleware = $routeAnnotation->getMiddleware();
        }

        return [
            'name' => $routeAnnotation->getName(),
            'priority' => $routeAnnotation->getPriority(),
            'methods' => $routeAnnotation->getMethods(),
            'pattern' => $pattern,
            'placeholders' => $placeholders,
            'middleware' => $middleware,
            'invokable' => $class->name . '::' . $method->name,
        ];
    }

    /**
     * Get group chain.
     *
     * @param \ReflectionClass  $class
     * @param GroupAnnotation   $groupAnnotation
     * @param GroupAnnotation[] $definedGroups
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return GroupAnnotation[]
     */
    protected function getGroupChain(
        \ReflectionClass $class,
        GroupAnnotation $groupAnnotation,
        array $definedGroups
    ): array {
        $groupChain = [
            $groupAnnotation->getName() => $groupAnnotation,
        ];

        $group = $groupAnnotation;
        while ($group->getGroup() !== '') {
            $referencedGroup = $group->getGroup();

            if (!array_key_exists($referencedGroup, $definedGroups)) {
                throw new \RuntimeException(
                    sprintf(
                        'Referenced group "%s" on class %s is not defined',
                        $referencedGroup,
                        $class->name
                    )
                );
            }

            if (array_key_exists($referencedGroup, $groupChain)) {
                throw new \RuntimeException(
                    sprintf(
                        'Circular reference detected with group "%s" on class %s',
                        $referencedGroup,
                        $class->name
                    )
                );
            }

            $groupChain[$referencedGroup] = $definedGroups[$referencedGroup];

            $group = $definedGroups[$referencedGroup];
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
}
