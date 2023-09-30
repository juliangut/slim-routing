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

namespace Jgut\Slim\Routing\Route;

use InvalidArgumentException;
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Psr\Http\Server\MiddlewareInterface;
use RuntimeException;

class RouteResolver
{
    public function __construct(
        protected Configuration $configuration,
    ) {}

    public function getName(RouteMetadata $route): ?string
    {
        if ($route->getName() === null) {
            return null;
        }

        $nameSegments = array_values(array_filter(array_map(
            static fn(GroupMetadata $group): ?string => $group->getPrefix(),
            $route->getGroupChain(),
        )));

        $nameSegments[] = $route->getName();

        return $this->configuration->getNamingStrategy()
            ->combine($nameSegments);
    }

    /**
     * @return list<string|MiddlewareInterface>
     */
    public function getMiddleware(RouteMetadata $route): array
    {
        $middleware = array_filter(array_map(
            static fn(GroupMetadata $group): array => $group->getMiddlewares(),
            array_reverse($route->getGroupChain()),
        ));
        array_unshift($middleware, $route->getMiddlewares());

        return array_values(array_merge(...$middleware));
    }

    /**
     * @throws RuntimeException
     */
    public function getPattern(RouteMetadata $route): string
    {
        $patterns = array_map(
            static fn(GroupMetadata $group): ?string => $group->getPattern(),
            $route->getGroupChain(),
        );
        $patterns[] = $route->getPattern();
        $patterns = array_filter($patterns);

        $pattern = '/' . (\count($patterns) === 0 ? '' : implode('/', $patterns));
        if ($pattern !== '/' && $this->configuration->hasTrailingSlash()) {
            $pattern .= '/';
        }
        $placeholders = $this->getPlaceholders($route);

        if ((bool) preg_match_all('/{([a-zA-Z_][a-zA-Z0-9_-]*)}/', $pattern, $parameter) !== false) {
            $parameters = $parameter[1];

            $duplicatedParameters = array_unique(array_diff_assoc($parameters, array_unique($parameters)));
            if (\count($duplicatedParameters) > 0) {
                throw new RuntimeException(
                    'There are duplicated route parameters: ' . implode(', ', $duplicatedParameters),
                );
            }

            foreach ($parameters as $param) {
                if (\array_key_exists($param, $placeholders)) {
                    $pattern = str_replace(
                        '{' . $param . '}',
                        sprintf('{%s:%s}', $param, $placeholders[$param]),
                        $pattern,
                    );
                }
            }
        }

        return $pattern;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return array<string, string>
     */
    protected function getPlaceholders(RouteMetadata $route): array
    {
        $aliases = $this->configuration->getPlaceholderAliases();

        $placeholders = array_filter(array_map(
            static fn(GroupMetadata $group): array => $group->getPlaceholders(),
            $route->getGroupChain(),
        ));
        $placeholders[] = $route->getPlaceholders();

        $placeholders = array_filter(array_merge(...$placeholders));

        return array_map(
            static function (string $pattern) use ($aliases): string {
                if (\array_key_exists($pattern, $aliases)) {
                    return $aliases[$pattern];
                }

                if (@preg_match('~^' . $pattern . '$~', '') !== false) {
                    return $pattern;
                }

                throw new InvalidArgumentException(
                    sprintf('Placeholder "%s" is not a known alias or a valid regex pattern.', $pattern),
                );
            },
            $placeholders,
        );
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return array<string, mixed>
     */
    public function getArguments(RouteMetadata $route): array
    {
        $arguments = array_map(
            static fn(GroupMetadata $group): array => $group->getArguments(),
            $route->getGroupChain(),
        );
        $arguments[] = $route->getArguments();

        return array_filter(array_merge(...$arguments));
    }

    /**
     * @param list<RouteMetadata> $routes
     *
     * @throws RuntimeException
     */
    public function checkDuplicatedRoutes(array $routes): void
    {
        $this->checkDuplicatedRouteNames($routes);
        $this->checkDuplicatedRoutePaths($routes);
    }

    /**
     * @param list<RouteMetadata> $routes
     *
     * @throws RuntimeException
     */
    protected function checkDuplicatedRouteNames(array $routes): void
    {
        $names = array_filter(array_map(
            fn(RouteMetadata $route): ?string => $this->getName($route),
            $routes,
        ));

        $duplicatedNames = array_unique(array_diff_assoc($names, array_unique($names)));
        if (\count($duplicatedNames) > 0) {
            throw new RuntimeException('There are duplicated route names: ' . implode(', ', $duplicatedNames));
        }
    }

    /**
     * @param list<RouteMetadata> $routes
     *
     * @throws RuntimeException
     */
    protected function checkDuplicatedRoutePaths(array $routes): void
    {
        $paths = array_map(
            function (RouteMetadata $route) {
                return array_map(
                    function (string $method) use ($route): string {
                        return sprintf(
                            '%s %s',
                            $method,
                            preg_replace('/{([a-zA-Z_][a-zA-Z0-9_-]*):/', '{', $this->getPattern($route)),
                        );
                    },
                    $route->getMethods(),
                );
            },
            $routes,
        );

        $paths = \count($paths) > 0 ? array_merge(...$paths) : [];

        $duplicatedPaths = array_unique(array_diff_assoc($paths, array_unique($paths)));
        if (\count($duplicatedPaths) > 0) {
            throw new RuntimeException('There are duplicated routes: ' . implode(', ', $duplicatedPaths));
        }
    }

    /**
     * @param list<RouteMetadata> $routesMetadata
     *
     * @return list<RouteMetadata>
     */
    public function sort(array $routesMetadata): array
    {
        return $this->stableUsort(
            $routesMetadata,
            static fn(RouteMetadata $routeA, RouteMetadata $routeB): int
                => $routeA->getPriority() <=> $routeB->getPriority(),
        );
    }

    /**
     * Stable usort. Keeps original order when sorting function returns 0.
     *
     * @param list<RouteMetadata>                         $array
     * @param callable(RouteMetadata, RouteMetadata): int $sortFunction
     *
     * @return list<RouteMetadata>
     */
    private function stableUsort(array $array, callable $sortFunction): array
    {
        $sortArray = [];

        $key = 0;
        foreach ($array as $route) {
            $sortArray[] = [$key, $route];

            ++$key;
        }

        usort(
            $sortArray,
            static function (array $itemA, array $itemB) use ($sortFunction): int {
                $result = $sortFunction($itemA[1], $itemB[1]);

                return $result === 0 ? $itemA[0] - $itemB[0] : $result;
            },
        );

        return array_values(array_map(
            static fn(array $item): RouteMetadata => $item[1],
            $sortArray,
        ));
    }
}
