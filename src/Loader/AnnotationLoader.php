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

/**
 * Classes routing loader.
 *
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
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

        $classList = $this->getClasses($routingSources);
        $groupList = $this->getNamedGroups($classList, $annotationReader);

        $loadedData = [];
        foreach ($classList as $class) {
            if ($class->isAbstract()) {
                continue;
            }

            /* @var RouterAnnotation $routerAnnotation */
            $routerAnnotation = $annotationReader->getClassAnnotation($class, RouterAnnotation::class);

            if ($routerAnnotation) {
                $loadedData[] = $this->getClassRoutes($class, $annotationReader, $groupList);
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

        $recursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $regexIterator = new \RegexIterator($recursiveIterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($regexIterator as $file) {
            $routingFiles[] = $this->loadSourceFromFile($file[0]);
        }
        sort($routingFiles);

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
            $classes[] = new \ReflectionClass($routingClass);
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
     * @param GroupAnnotation[] $groupList
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function getClassRoutes(
        \ReflectionClass $class,
        AnnotationReader $annotationReader,
        array $groupList
    ): array {
        $routes = [];

        /* @var GroupAnnotation $groupAnnotation */
        $groupAnnotation = $annotationReader->getClassAnnotation($class, GroupAnnotation::class);

        foreach ($class->getMethods() as $method) {
            /* @var RouteAnnotation $routeAnnotation */
            $routeAnnotation = $annotationReader->getMethodAnnotation($method, RouteAnnotation::class);

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

                $routes[] = $this->getCompiledRoute(
                    $class,
                    $method,
                    $routeAnnotation,
                    $groupList,
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
     * @param GroupAnnotation[]    $groupList
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
        array $groupList,
        GroupAnnotation $groupAnnotation = null
    ): array {
        $groupChain = $this->getGroupChain($class, $groupList, $groupAnnotation);

        return [
            'name' => $this->getCompoundName($routeAnnotation, $groupChain),
            'priority' => $routeAnnotation->getPriority(),
            'methods' => $routeAnnotation->getMethods(),
            'pattern' => $this->getCompoundPattern($routeAnnotation, $groupChain),
            'placeholders' => $this->getCompoundPlaceholders($routeAnnotation, $groupChain),
            'middleware' => $this->getCompoundMiddleware($routeAnnotation, $groupChain),
            'invokable' => [$class->name,  $method->name],
        ];
    }

    /**
     * Get group annotations chain.
     *
     * @param \ReflectionClass  $class
     * @param GroupAnnotation[] $groupList
     * @param GroupAnnotation   $groupAnnotation
     *
     * @throws \RuntimeException
     *
     * @return GroupAnnotation[]
     */
    protected function getGroupChain(
        \ReflectionClass $class,
        array $groupList,
        GroupAnnotation $groupAnnotation = null
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

            if (!array_key_exists($parentGroup, $groupList)) {
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

            $groupChain[$parentGroup] = $group = $groupList[$parentGroup];
        }

        return array_reverse(array_values($groupChain));
    }

    /**
     * Get compound name.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupChain
     *
     * @return string
     */
    protected function getCompoundName(
        RouteAnnotation $routeAnnotation,
        array $groupChain
    ): string {
        $routeName = $routeAnnotation->getName();
        if ($routeName === '') {
            return '';
        }

        $names = array_map(
            function (GroupAnnotation $groupAnnotation) {
                return $groupAnnotation->getPrefix();
            },
            $groupChain
        );
        $names[] = $routeName;

        return implode('_', array_filter($names));
    }

    /**
     * Get compound path.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupChain
     *
     * @return string
     */
    protected function getCompoundPattern(
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

        return preg_replace('!//+!', '/', implode('', array_filter($patterns)));
    }

    /**
     * Get compound placeholders.
     *
     * @param RouteAnnotation   $routeAnnotation
     * @param GroupAnnotation[] $groupChain
     *
     * @return array
     */
    protected function getCompoundPlaceholders(
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
    protected function getCompoundMiddleware(
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
}
