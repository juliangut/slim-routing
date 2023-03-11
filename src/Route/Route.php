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

use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Transformer\ParameterTransformer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Routing\Route as SlimRoute;
use Slim\Routing\RouteGroup;

class Route extends SlimRoute
{
    protected ?RouteMetadata $metadata;

    /**
     * @param array<string>                          $methods
     * @param array<RouteGroup>                      $groups
     * @param string|array<string>|callable(): mixed $callable
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        array $methods,
        string $pattern,
        $callable,
        ResponseFactoryInterface $responseFactory,
        CallableResolverInterface $callableResolver,
        ?RouteMetadata $metadata = null,
        ?ContainerInterface $container = null,
        ?InvocationStrategyInterface $invocationStrategy = null,
        array $groups = [],
        int $identifier = 0
    ) {
        parent::__construct(
            $methods,
            $pattern,
            $callable,
            $responseFactory,
            $callableResolver,
            $container,
            $invocationStrategy,
            $groups,
            $identifier,
        );

        $this->metadata = $metadata;
    }

    public function getMetadata(): ?RouteMetadata
    {
        return $this->metadata;
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->groupMiddlewareAppended) {
            $this->appendGroupMiddlewareToRoute();
        }

        if ($this->metadata !== null
            && $this->metadata->isXmlHttpRequest()
            && mb_strtolower($request->getHeaderLine('X-Requested-With')) !== 'xmlhttprequest'
        ) {
            return $this->responseFactory->createResponse(400);
        }

        return $this->middlewareDispatcher->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $arguments = $this->arguments;
        $this->arguments = $this->transformArguments($arguments);

        try {
            $response = parent::handle($request);
        } finally {
            $this->arguments = $arguments;
        }

        return $response;
    }

    /**
     * @param array<string, string> $arguments
     *
     * @throws RuntimeException
     *
     * @return array<string, mixed>
     */
    protected function transformArguments(array $arguments): array
    {
        if ($this->metadata === null) {
            return $arguments;
        }

        $transformer = $this->metadata->getTransformer();
        if ($transformer !== null) {
            if (isset($this->container)) {
                $transformer = $this->container->get($transformer);
            }

            if (!$transformer instanceof ParameterTransformer) {
                throw new RuntimeException(sprintf(
                    'Parameter transformer should implement %s, "%s" given.',
                    ParameterTransformer::class,
                    \is_object($transformer) ? \get_class($transformer) : \gettype($transformer),
                ));
            }

            $arguments = $transformer->transform($arguments, $this->getRouteParameters($this->metadata));
        }

        return $arguments;
    }

    /**
     * @return array<string, string>
     */
    protected function getRouteParameters(RouteMetadata $route): array
    {
        $parameters = array_filter(array_map(
            static fn(GroupMetadata $group): array => $group->getParameters(),
            $route->getGroupChain(),
        ));
        array_unshift($parameters, $route->getParameters());

        return array_filter(array_merge(...$parameters));
    }
}
