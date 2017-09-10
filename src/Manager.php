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

use Jgut\Slim\Routing\Loader\LoaderInterface;
use Jgut\Slim\Routing\Source\SourceFactory;
use Jgut\Slim\Routing\Source\SourceInterface;
use Psr\Container\ContainerInterface;

/**
 * Routing manager.
 */
class Manager
{
    /**
     * Routing configuration.
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Loader list.
     *
     * @var array
     */
    protected $loaders = [];

    /**
     * Route compiler.
     *
     * @var RouteCompiler
     */
    protected $compiler;

    /**
     * Routing Manager constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Register routes into Slim router.
     *
     * @param ContainerInterface $container
     *
     * @throws \RuntimeException
     */
    public function registerRoutes(ContainerInterface $container)
    {
        $router = $container->get('router');
        $buffering = $container->get('settings')['outputBuffering'];

        foreach ($this->getRoutes() as $route) {
            /* @var \Slim\Route $slimRoute */
            $slimRoute = $router->map(
                $route->getMethods(),
                $this->getCompoundPath($route),
                $route->getInvokable()
            );
            $slimRoute->setContainer($container);
            $slimRoute->setOutputBuffering($buffering);

            if ($route->getName() !== '') {
                $slimRoute->setName($route->getName());
            }

            foreach ($route->getMiddleware() as $middleware) {
                $slimRoute->add($middleware);
            }
        }
    }

    /**
     * Get routes.
     *
     * @throws \RuntimeException
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        $routes = [];
        foreach ($this->configuration->getSources() as $source) {
            $source = SourceFactory::getSource($source);

            $routingSources = $this->getLoader($source)->load($source->getPaths());
            $routes[] = $this->getCompiler()->getRoutes($routingSources);
        }

        $routes = count($routes) ? array_merge(...$routes) : [];

        $this->checkDuplicatedRoutes($routes);

        $this->stableUsort(
            $routes,
            function (Route $routeA, Route $routeB) {
                return $routeA->getPriority() <=> $routeB->getPriority();
            }
        );

        return $routes;
    }

    /**
     * Check duplicated routes.
     *
     * @param Route[] $routes
     *
     * @throws \RuntimeException
     */
    protected function checkDuplicatedRoutes(array $routes)
    {
        $paths = [];
        foreach ($routes as $route) {
            /* @var Route $route */
            $paths[] = array_map(
                function (string $method) use ($route) {
                    return sprintf('%s %s', $method, $this->getCompoundPath($route));
                },
                $route->getMethods()
            );
        }

        $paths = count($paths) ? array_merge(...$paths) : [];

        $duplicatedPaths = array_unique(array_diff_assoc($paths, array_unique($paths)));
        if (count($duplicatedPaths)) {
            throw new \RuntimeException('There are duplicated routes: ' . implode(', ', $duplicatedPaths));
        }
    }

    /**
     * Get compound path.
     *
     * @param Route $route
     *
     * @return string
     */
    protected function getCompoundPath(Route $route): string
    {
        $path = $route->getPattern();
        $regex = $route->getPlaceholders();

        if (preg_match_all('/\{(.+)\}/', $path, $parameter)) {
            $parameter = array_column($parameter, 0);

            if (array_key_exists($parameter[1], $regex)) {
                $path = str_replace(
                    $parameter[0],
                    sprintf('{%s:%s}', $parameter[1], $regex[$parameter[1]]),
                    $path
                );
            }
        }

        return $path;
    }

    /**
     * Get loader from source.
     *
     * @param SourceInterface $source
     *
     * @return LoaderInterface
     */
    protected function getLoader(SourceInterface $source): LoaderInterface
    {
        $loaderClass = $source->getLoaderClass();

        if (!array_key_exists($loaderClass, $this->loaders)) {
            $this->loaders[$loaderClass] = new $loaderClass($this->configuration);
        }

        return $this->loaders[$loaderClass];
    }

    /**
     * Get route compiler.
     *
     * @return RouteCompiler
     */
    protected function getCompiler(): RouteCompiler
    {
        if ($this->compiler === null) {
            // @codeCoverageIgnoreStart
            $this->compiler = new RouteCompiler($this->configuration);
            // @codeCoverageIgnoreEnd
        }

        return $this->compiler;
    }

    /**
     * Set route compiler.
     *
     * @param RouteCompiler $compiler
     */
    public function setCompiler(RouteCompiler $compiler)
    {
        $this->compiler = $compiler;
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
