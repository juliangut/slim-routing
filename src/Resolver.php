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

use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;

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
     * @param RouteMetadata $route
     *
     * @return string|null
     */
    public function getName(RouteMetadata $route)
    {
        if ($route->getName() === null) {
            return null;
        }

        $nameSegments = array_filter(array_map(
            function (GroupMetadata $group) {
                return $group->getPrefix();
            },
            $route->getGroupChain()
        ));

        $nameSegments[] = $route->getName();

        return $this->configuration->getNamingStrategy()->combine($nameSegments);
    }

    /**
     * Get route middleware.
     *
     * @param RouteMetadata $route
     *
     * @return callable[]|string[]
     */
    public function getMiddleware(RouteMetadata $route): array
    {
        $middleware = array_filter(array_map(
            function (GroupMetadata $group) {
                return $group->getMiddleware();
            },
            array_reverse($route->getGroupChain())
        ));
        array_unshift($middleware, $route->getMiddleware());

        return array_filter(array_merge(...$middleware));
    }

    /**
     * Get route pattern.
     *
     * @param RouteMetadata $route
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getPattern(RouteMetadata $route): string
    {
        $groupChain = $route->getGroupChain();

        $patterns = array_map(
            function (GroupMetadata $group) {
                return $group->getPattern();
            },
            $groupChain
        );
        $patterns[] = $route->getPattern();
        $patterns = array_filter($patterns);

        $pattern = '/' . (count($patterns) === 0 ? '' : implode('/', $patterns));
        $placeholders = $this->getPlaceholders($route);

        if (preg_match_all('/\{([^}]+)\}/', $pattern, $parameter)) {
            $parameters = $parameter[1];

            $duplicatedParameters = array_unique(array_diff_assoc($parameters, array_unique($parameters)));
            if (count($duplicatedParameters)) {
                throw new \RuntimeException(
                    'There are duplicated route parameters: ' . implode(', ', $duplicatedParameters)
                );
            }

            foreach ($parameters as $parameter) {
                if (array_key_exists($parameter, $placeholders)) {
                    $pattern = str_replace(
                        '{' . $parameter . '}',
                        sprintf('{%s:%s}', $parameter, $placeholders[$parameter]),
                        $pattern
                    );
                }
            }
        }

        return $pattern;
    }

    /**
     * Get route placeholders.
     *
     * @param RouteMetadata $route
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getPlaceholders(RouteMetadata $route): array
    {
        $aliases = $this->configuration->getPlaceholderAliases();

        $placeholders = array_filter(array_map(
            function (GroupMetadata $group) {
                return $group->getPlaceholders();
            },
            $route->getGroupChain()
        ));
        $placeholders[] = $route->getPlaceholders();
        $placeholders = count($placeholders) ? array_filter(array_merge(...$placeholders)) : [];

        return array_map(
            function (string $pattern) use ($aliases) {
                if (array_key_exists($pattern, $aliases)) {
                    return $aliases[$pattern];
                }

                if (@preg_match('~^' . $pattern . '$~', '') !== false) {
                    return $pattern;
                }

                throw new \InvalidArgumentException(
                    sprintf('Placeholder "%s" is not a known alias or a valid regex pattern', $pattern)
                );
            },
            $placeholders
        );
    }

    /**
     * Check route duplication.
     *
     * @param RouteMetadata[] $routes
     *
     * @throws \RuntimeException
     */
    public function checkDuplicatedRoutes(array $routes)
    {
        $this->checkDuplicatedRouteNames($routes);
        $this->checkDuplicatedRoutePaths($routes);
    }

    /**
     * Check duplicated route names.
     *
     * @param RouteMetadata[] $routes
     *
     * @throws \RuntimeException
     */
    protected function checkDuplicatedRouteNames(array $routes)
    {
        $names = array_filter(array_map(
            function (RouteMetadata $route) {
                return $this->getName($route);
            },
            $routes
        ));

        $duplicatedNames = array_unique(array_diff_assoc($names, array_unique($names)));
        if (count($duplicatedNames)) {
            throw new \RuntimeException('There are duplicated route names: ' . implode(', ', $duplicatedNames));
        }
    }

    /**
     * Check duplicated route paths.
     *
     * @param RouteMetadata[] $routes
     *
     * @throws \RuntimeException
     */
    protected function checkDuplicatedRoutePaths(array $routes)
    {
        $paths = array_map(
            function (RouteMetadata $route) {
                return array_map(
                    function (string $method) use ($route) {
                        return sprintf(
                            '%s %s',
                            $method,
                            preg_replace('/\{.+:/', '{', $this->getPattern($route))
                        );
                    },
                    $route->getMethods()
                );
            },
            $routes
        );

        $paths = count($paths) ? array_merge(...$paths) : [];

        $duplicatedPaths = array_unique(array_diff_assoc($paths, array_unique($paths)));

        if (count($duplicatedPaths)) {
            throw new \RuntimeException('There are duplicated routes: ' . implode(', ', $duplicatedPaths));
        }
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
}
