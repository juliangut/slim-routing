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

/**
 * Routing compiler.
 */
class RouteCompiler
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
     * Get parsed routes.
     *
     * @param array $routingSources
     *
     * @return Route[]
     */
    public function getRoutes(array $routingSources): array
    {
        $routes = [];

        foreach ($this->getCompoundRoutes($routingSources) as $compoundRoute) {
            $routes[] = (new Route())
                ->setName($compoundRoute['name'])
                ->setPriority($compoundRoute['priority'])
                ->setMethods($compoundRoute['methods'])
                ->setPattern($compoundRoute['pattern'])
                ->setPlaceholders($compoundRoute['placeholders'])
                ->setMiddleware($compoundRoute['middleware'])
                ->setInvokable($compoundRoute['invokable']);
        }

        return $routes;
    }

    /**
     * Get defined routes.
     *
     * @param array $sources
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function getCompoundRoutes(array $sources): array
    {
        $routes = [];

        foreach ($sources as $source) {
            if (!is_array($source)) {
                throw new \InvalidArgumentException('Routing definition must be an array');
            }

            if (array_key_exists('routes', $source)) {
                $groupRoutes = $this->getCompoundRoutes($source['routes']);

                $routes[] = $this->getCompoundGroupRoutes($source, $groupRoutes);
            } else {
                $routes[] = [$this->getProcessedRoute($source)];
            }
        }

        $routes = count($routes) ? array_merge(...$routes) : [];

        foreach ($routes as $route) {
            $this->checkPath($route['pattern'], $route['placeholders']);
        }

        return $routes;
    }

    /**
     * Get compound group routes.
     *
     * @param array $group
     * @param array $routes
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function getCompoundGroupRoutes(array $group, array $routes): array
    {
        $pattern = $this->getSourcePattern($group);
        $placeholders = $this->getSourcePlaceholders($group);
        $middleware = $this->getSourceMiddleware($group);

        return array_map(
            function ($route) use ($pattern, $placeholders, $middleware) {
                $route['pattern'] = preg_replace('!//+!', '/', $pattern . $route['pattern']);
                $route['placeholders'] = array_merge($placeholders, $route['placeholders']);
                $route['middleware'] = array_merge($route['middleware'], $middleware);

                return $route;
            },
            $routes
        );
    }

    /**
     * Get defined route.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function getProcessedRoute(array $source): array
    {
        return [
            'name' => $this->getSourceName($source),
            'methods' => $this->getSourceMethods($source),
            'priority' => $this->getSourcePriority($source),
            'pattern' => $this->getSourcePattern($source),
            'placeholders' => $this->getSourcePlaceholders($source),
            'middleware' => $this->getSourceMiddleware($source),
            'invokable' => $this->getSourceInvokable($source),
        ];
    }

    /**
     * Get defined route name.
     *
     * @param array $source
     *
     * @return string
     */
    protected function getSourceName(array $source): string
    {
        return array_key_exists('name', $source) ? $source['name'] : '';
    }

    /**
     * Get defined route methods.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getSourceMethods(array $source): array
    {
        if (!array_key_exists('methods', $source)) {
            return ['GET'];
        }

        $methods = [];

        $sourceMethods = $source['methods'];
        if (!is_array($sourceMethods)) {
            $sourceMethods = [$sourceMethods];
        }

        foreach (array_filter($sourceMethods) as $method) {
            if (!is_string($method)) {
                throw new \InvalidArgumentException(
                    sprintf('Route methods must be a string or string array. "%s" given', gettype($method))
                );
            }

            $methods[] = strtoupper(trim($method));
        }

        $methods = array_unique($methods);

        if (!count($methods)) {
            throw new \InvalidArgumentException('Route methods can not be empty');
        }

        if (in_array('ANY', $methods, true)) {
            if (count($methods) > 1) {
                throw new \InvalidArgumentException('Route "ANY" method cannot be defined with other methods');
            }

            $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        }

        return $methods;
    }

    /**
     * Get defined route priority.
     *
     * @param array $source
     *
     * @return int
     */
    protected function getSourcePriority(array $source): int
    {
        return array_key_exists('priority', $source) ? (int) $source['priority'] : 0;
    }

    /**
     * Get defined route pattern.
     *
     * @param array $source
     *
     * @return string
     */
    protected function getSourcePattern(array $source): string
    {
        return array_key_exists('pattern', $source) ? $source['pattern'] : '/';
    }

    /**
     * Get defined route placeholders.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getSourcePlaceholders(array $source): array
    {
        if (!array_key_exists('placeholders', $source)) {
            return [];
        }

        $placeholders = $source['placeholders'];

        array_walk(
            $placeholders,
            function (string &$pattern, $key) {
                if (!is_string($key)) {
                    throw new \InvalidArgumentException('Placeholder keys must be all strings');
                }

                $pattern = $this->getPlaceholderPattern($pattern);
            }
        );

        return $placeholders;
    }

    /**
     * Get defined route middleware.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getSourceMiddleware(array $source): array
    {
        if (!array_key_exists('middleware', $source)) {
            return [];
        }

        $middlewareList = $source['middleware'];
        if (!is_array($middlewareList)) {
            $middlewareList = [$middlewareList];
        }

        foreach ($middlewareList as $middleware) {
            if (!is_string($middleware)) {
                throw new \InvalidArgumentException(
                    sprintf('Middleware must be a string or string array. "%s" given', gettype($middleware))
                );
            }
        }

        return $middlewareList;
    }

    /**
     * Get defined route invokable.
     *
     * @param array $source
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return callable
     */
    protected function getSourceInvokable(array $source)
    {
        if (!array_key_exists('invokable', $source)) {
            throw new \RuntimeException('Route invokable definition missing');
        }

        $invokable = $source['invokable'];

        if (!is_string($invokable) && !is_array($invokable) && !is_callable($invokable)) {
            throw new \InvalidArgumentException('Route invokable does not seam to be supported by Slim router');
        }

        return $invokable;
    }

    /**
     * Check if duplicated placeholders are present on route patterns.
     *
     * @param string $pattern
     * @param array  $placeholders
     *
     * @throws \RuntimeException
     */
    protected function checkPath(string $pattern, array $placeholders)
    {
        if (preg_match_all('/\{([^{]+)\}/', $pattern, $patternPlaceholders)) {
            if (count($patternPlaceholders[1]) !== count(array_unique($patternPlaceholders[1]))) {
                throw new \RuntimeException(
                    sprintf('Pattern "%s" contains duplicated placeholders', $pattern)
                );
            }
        }

        $unknownPlaceholders = array_diff(array_keys($placeholders), $patternPlaceholders[1]);
        if (count($unknownPlaceholders)) {
            throw new \RuntimeException(
                sprintf(
                    'Pattern "%s" does not contain the following placeholders: %s',
                    $pattern,
                    implode(', ', $unknownPlaceholders)
                )
            );
        }
    }

    /**
     * Get placeholder pattern.
     *
     * @param string $pattern
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function getPlaceholderPattern(string $pattern): string
    {
        $aliases = $this->configuration->getPlaceholderAliases();

        if (array_key_exists($pattern, $aliases)) {
            return $aliases[$pattern];
        }

        if (@preg_match('~^' . $pattern . '$~', '') !== false) {
            return $pattern;
        }

        throw new \InvalidArgumentException(
            sprintf('Placeholder pattern "%s" is not a known alias or a valid regex', $pattern)
        );
    }
}
