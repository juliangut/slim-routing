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

namespace Jgut\Slim\Routing;

use Jgut\Slim\Routing\Mapping\RouteMetadata;

/**
 * Routing resolver.
 */
class Resolver
{
    /**
     * Routing configuration.
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * RouteCompiler constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get route name.
     *
     * @param RouteMetadata $routeMetadata
     *
     * @return string
     */
    public function getName(RouteMetadata $routeMetadata): string
    {
        $name = $routeMetadata->getName();

        return $name === ''
            ? ''
            : $this->configuration->getNamingStrategy()->combine(array_merge($routeMetadata->getPrefixes(), [$name]));
    }

    /**
     * Get route methods.
     *
     * @param RouteMetadata $routeMetadata
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    public function getMethods(RouteMetadata $routeMetadata): array
    {
        $methods = $routeMetadata->getMethods();

        if (in_array('ANY', $methods, true)) {
            if (count($methods) > 1) {
                throw new \InvalidArgumentException('Route "ANY" method cannot be defined with other methods');
            }

            return ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        }

        return $methods;
    }

    /**
     * Get route pattern.
     *
     * @param RouteMetadata $routeMetadata
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getPattern(RouteMetadata $routeMetadata): string
    {
        $pattern = $routeMetadata->getPattern() === '' ? '/' : $routeMetadata->getPattern();
        $placeholders = $this->getPlaceholders($routeMetadata);

        if (preg_match_all('/\{(.+)\}/', $pattern, $parameter)) {
            $parameter = array_column($parameter, 0);

            if (array_key_exists($parameter[1], $placeholders)) {
                $pattern = str_replace(
                    $parameter[0],
                    sprintf('{%s:%s}', $parameter[1], $placeholders[$parameter[1]]),
                    $pattern
                );
            }
        }

        return $pattern;
    }

    /**
     * Sort routes.
     *
     * @param RouteMetadata[] $routesMetadata
     *
     * @return RouteMetadata[]
     */
    public function sort(array $routesMetadata): array
    {
        $this->stableUsort(
            $routesMetadata,
            function (RouteMetadata $routeA, RouteMetadata $routeB) {
                return $routeA->getPriority() <=> $routeB->getPriority();
            }
        );

        return $routesMetadata;
    }

    /**
     * Check route duplication.
     *
     * @param array $routesMetadata
     *
     * @throws \RuntimeException
     */
    public function checkDuplicatedRoutes(array $routesMetadata)
    {
        $this->checkDuplicatedRouteNames($routesMetadata);
        $this->checkDuplicatedRoutePaths($routesMetadata);
    }

    /**
     * Check duplicated route names.
     *
     * @param RouteMetadata[] $routesMetadata
     *
     * @throws \RuntimeException
     */
    protected function checkDuplicatedRouteNames(array $routesMetadata)
    {
        $names = array_filter(array_map(
            function (RouteMetadata $routeMetadata) {
                return $routeMetadata->getName();
            },
            $routesMetadata
        ));

        $duplicatedNames = array_unique(array_diff_assoc($names, array_unique($names)));
        if (count($duplicatedNames)) {
            throw new \RuntimeException('There are duplicated route names: ' . implode(', ', $duplicatedNames));
        }
    }

    /**
     * Check duplicated route paths.
     *
     * @param RouteMetadata[] $routesMetadata
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function checkDuplicatedRoutePaths(array $routesMetadata)
    {
        $paths = array_map(
            function (RouteMetadata $routeMetadata) {
                return array_map(
                    function (string $method) use ($routeMetadata) {
                        return sprintf(
                            '%s %s',
                            $method,
                            preg_replace('/\{.+:/', '{', $this->getPattern($routeMetadata))
                        );
                    },
                    $this->getMethods($routeMetadata)
                );
            },
            $routesMetadata
        );

        $paths = count($paths) ? array_merge(...$paths) : [];

        $duplicatedPaths = array_unique(array_diff_assoc($paths, array_unique($paths)));
        if (count($duplicatedPaths)) {
            throw new \RuntimeException('There are duplicated routes: ' . implode(', ', $duplicatedPaths));
        }
    }

    /**
     * Stable usort.
     * Keeps original order when sorting function returns 0.
     *
     * @param array    $array
     * @param callable $sortFunction
     *
     * @return bool
     */
    private function stableUsort(array &$array, callable $sortFunction): bool
    {
        array_walk(
            $array,
            function (&$item, $key) {
                $item = [$key, $item];
            }
        );

        $result = usort(
            $array,
            function (array $itemA, array $itemB) use ($sortFunction) {
                $result = $sortFunction($itemA[1], $itemB[1]);

                return $result === 0 ? $itemA[0] - $itemB[0] : $result;
            }
        );

        array_walk(
            $array,
            function (&$item) {
                $item = $item[1];
            }
        );

        return $result;
    }

    /**
     * Get route placeholders.
     *
     * @param RouteMetadata $routeMetadata
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getPlaceholders(RouteMetadata $routeMetadata): array
    {
        $aliases = $this->configuration->getPlaceholderAliases();

        return array_map(
            function (string $pattern) use ($aliases) {
                if (array_key_exists($pattern, $aliases)) {
                    return $aliases[$pattern];
                }

                if (@preg_match('~^' . $pattern . '$~', '') !== false) {
                    return $pattern;
                }

                throw new \InvalidArgumentException(
                    sprintf('Placeholder pattern "%s" is not a known alias or a valid regex', $pattern)
                );
            },
            $routeMetadata->getPlaceholders()
        );
    }
}
