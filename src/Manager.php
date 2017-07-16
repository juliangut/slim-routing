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

use Jgut\Slim\Routing\Compiler\CompilerInterface;
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
     * Compiler list.
     *
     * @var array
     */
    protected $compilers = [];

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
     * Get defined routes.
     *
     * @throws \RuntimeException
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        $compilationPath = $this->configuration->getCompilationPath();
        $compilationFile = $compilationPath ? rtrim($compilationPath) . '/CompiledRoutes.php' : null;

        if ($compilationPath && file_exists($compilationFile) && is_readable($compilationFile)) {
            $routes = require $compilationFile;

            if (!is_array($routes)) {
                throw new \RuntimeException(sprintf('%s file should return an array', $compilationFile));
            }
        } else {
            $routes = $this->loadRoutes();

            if ($compilationPath) {
                file_put_contents(
                    $compilationFile,
                    sprintf('<?php%1$s%1$sreturn %2$s;', "\n", var_export($routes, true))
                );
            }
        }

        return $routes;
    }

    /**
     * Get routes.
     *
     * @throws \RuntimeException
     *
     * @return Route[]
     */
    protected function loadRoutes(): array
    {
        $routes = [];
        foreach ($this->configuration->getSources() as $source) {
            $source = SourceFactory::getSource($source);

            $routingSources = $this->getLoader($source)->load($source->getPaths());
            $routes[] = $this->getCompiler($source)->getRoutes($routingSources);
        }

        $routes = count($routes) ? array_merge(...$routes) : [];

        $this->checkDuplicatedRoutes($routes);

        usort(
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
            $this->loaders[$loaderClass] = new $loaderClass();
        }

        return $this->loaders[$loaderClass];
    }

    /**
     * Get compiler from source.
     *
     * @param SourceInterface $source
     *
     * @return CompilerInterface
     */
    protected function getCompiler(SourceInterface $source): CompilerInterface
    {
        $compilerClass = $source->getCompilerClass();

        if (!array_key_exists($compilerClass, $this->compilers)) {
            $this->compilers[$compilerClass] = new $compilerClass();
        }

        return $this->compilers[$compilerClass];
    }
}
