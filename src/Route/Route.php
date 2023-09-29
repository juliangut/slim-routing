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
use Slim\Interfaces\RouteGroupInterface;
use Slim\Routing\Route as SlimRoute;

class Route extends SlimRoute
{
    /**
     * @param list<string>              $methods
     * @param string|callable(): mixed  $callable
     * @param list<RouteGroupInterface> $groups
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        array $methods,
        string $pattern,
        $callable,
        ResponseFactoryInterface $responseFactory,
        CallableResolverInterface $callableResolver,
        protected ?RouteMetadata $metadata = null,
        ?ContainerInterface $container = null,
        ?InvocationStrategyInterface $invocationStrategy = null,
        array $groups = [],
        int $identifier = 0,
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
        $this->arguments = $this->transformParameters($arguments);

        try {
            $response = parent::handle($request);
        } finally {
            $this->arguments = $arguments;
        }

        return $response;
    }

    /**
     * @param array<string, string> $parameters
     *
     * @throws RuntimeException
     *
     * @return array<string, mixed>
     */
    protected function transformParameters(array $parameters): array
    {
        $transformer = $this->metadata?->getTransformer();
        if ($this->metadata === null || $transformer === null) {
            return $parameters;
        }

        if ($this->container !== null) {
            $transformer = $this->container->get($transformer);
        }
        if (!$transformer instanceof ParameterTransformer) {
            throw new RuntimeException(sprintf(
                'Parameter transformer should implement %s, "%s" given.',
                ParameterTransformer::class,
                \is_object($transformer) ? $transformer::class : \gettype($transformer),
            ));
        }

        $definitions = $this->getRouteParametersDefinitions($this->metadata);

        array_walk(
            $parameters,
            function (&$parameterValue, string $parameterName) use ($definitions, $transformer): void {
                if (\array_key_exists($parameterName, $definitions)) {
                    $parameterType = $definitions[$parameterName];

                    if (\in_array($parameterType, ['string', 'int', 'float', 'bool'], true)) {
                        $parameterValue = $this->transformToPrimitive($parameterType, $parameterValue);
                    } elseif ($transformer->supports($parameterName, $parameterType)) {
                        $parameterValue = $transformer->transform($parameterName, $parameterType, $parameterValue);
                    }
                }
            },
        );

        return $parameters;
    }

    protected function transformToPrimitive(string $type, string $value): float|bool|int|string
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => \in_array(trim($value), ['1', 'on', 'yes', 'true'], true),
            default => $value,
        };
    }

    /**
     * @return array<string, string>
     */
    protected function getRouteParametersDefinitions(RouteMetadata $route): array
    {
        $parameters = array_filter(array_map(
            static fn(GroupMetadata $group): array => $group->getParameters(),
            $route->getGroupChain(),
        ));
        array_unshift($parameters, $route->getParameters());

        return array_filter(array_merge(...$parameters));
    }
}
