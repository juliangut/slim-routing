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
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Routing\Route as SlimRoute;

/**
 * Metadata aware route.
 */
class Route extends SlimRoute
{
    /**
     * Route metadata.
     *
     * @var RouteMetadata|null
     */
    protected $metadata;

    /**
     * Route constructor.
     *
     * @param string[]                         $methods
     * @param string                           $pattern
     * @param callable                         $callable
     * @param ResponseFactoryInterface         $responseFactory
     * @param CallableResolverInterface        $callableResolver
     * @param RouteMetadata|null               $metadata
     * @param ContainerInterface|null          $container
     * @param InvocationStrategyInterface|null $invocationStrategy
     * @param \Slim\Routing\RouteGroup[]       $groups
     * @param int                              $identifier
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
            $identifier
        );

        $this->metadata = $metadata;
    }

    /**
     * Get route metadata.
     *
     * @return RouteMetadata|null
     */
    public function getMetadata(): ?RouteMetadata
    {
        return $this->metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->groupMiddlewareAppended) {
            $this->appendGroupMiddlewareToRoute();
        }

        if ($this->metadata !== null
            && $this->metadata->isXmlHttpRequest()
            && strtolower($request->getHeaderLine('X-Requested-With')) !== 'xmlhttprequest'
        ) {
            return $this->responseFactory->createResponse(400);
        }

        return $this->middlewareDispatcher->handle($request);
    }

    /**
     * {@inheritdoc}
     */
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
     * Transform route arguments.
     *
     * @param mixed[] $arguments
     *
     * @throws \RuntimeException
     *
     * @return mixed[]
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
                throw new \RuntimeException(sprintf(
                    'Parameter transformer should implement %s, "%s" given',
                    ParameterTransformer::class,
                    \is_object($transformer) ? \get_class($transformer) : \gettype($transformer)
                ));
            }

            $arguments = $transformer->transform($arguments, $this->getRouteParameters($this->metadata));
        }

        return $arguments;
    }

    /**
     * Get route parameters.
     *
     * @param RouteMetadata $route
     *
     * @return mixed[]
     */
    protected function getRouteParameters(RouteMetadata $route): array
    {
        $parameters = array_filter(array_map(
            function (GroupMetadata $group): array {
                return $group->getParameters();
            },
            $route->getGroupChain()
        ));
        array_unshift($parameters, $route->getParameters());

        return array_filter(array_merge(...$parameters));
    }
}
