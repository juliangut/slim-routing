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
     * @param list<string>                                   $methods
     * @param string|array{string, string}|callable(): mixed $callable
     * @param list<RouteGroupInterface>                      $groups
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
        if (\count($arguments) !== 0) {
            $this->arguments = $this->transformParameters($arguments);
        }

        try {
            $response = parent::handle($request);
        } finally {
            $this->arguments = $arguments;
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws RuntimeException
     *
     * @return array<string, mixed>
     */
    protected function transformParameters(array $parameters): array
    {
        $transformers = $this->getTransformers();
        if (\count($transformers) === 0) {
            return $parameters;
        }

        $definitions = $this->getRouteParametersDefinitions();

        $transformed = [];
        foreach ($transformers as $transformer) {
            foreach ($parameters as $parameterName => $parameterValue) {
                if (!\array_key_exists($parameterName, $definitions)) {
                    $transformed[$parameterName] = $parameterValue;
                } else {
                    $transformed[$parameterName] = $this->transformParameter(
                        $transformer,
                        $parameterName,
                        $definitions[$parameterName],
                        $parameterValue,
                    );
                }

                unset($parameters[$parameterName]);
            }

            if (\count($parameters) === 0) {
                break;
            }
        }

        return $transformed;
    }

    protected function transformParameter(
        ParameterTransformer $transformer,
        string $parameterName,
        string $parameterType,
        mixed $parameterValue,
    ): mixed {
        if (\is_string($parameterValue) && \in_array($parameterType, ['string', 'int', 'float', 'bool'], true)) {
            return match ($parameterType) {
                'int' => (int) $parameterValue,
                'float' => (float) $parameterValue,
                'bool' => \in_array(trim($parameterValue), ['1', 'on', 'yes', 'true'], true),
                default => $parameterValue,
            };
        }

        return $transformer->supports($parameterName, $parameterType)
            ? $transformer->transform($parameterName, $parameterType, $parameterValue)
            : $parameterValue;
    }

    /**
     * @throws RuntimeException
     *
     * @return list<ParameterTransformer>
     */
    protected function getTransformers(): array
    {
        if ($this->metadata === null) {
            return [];
        }

        return array_values(array_unique(array_map(
            function ($transformer): ParameterTransformer {
                $resolved = $this->container?->get($transformer);
                if (!$resolved instanceof ParameterTransformer) {
                    throw new RuntimeException(sprintf(
                        'Parameter transformer "%s" could not be resolved to a "%s", "%s" given.',
                        $transformer,
                        ParameterTransformer::class,
                        \is_object($resolved) ? $resolved::class : \gettype($resolved),
                    ));
                }

                return $resolved;
            },
            $this->metadata->getTransformers(),
        )));
    }

    /**
     * @return array<string, string>
     */
    protected function getRouteParametersDefinitions(): array
    {
        if ($this->metadata === null) {
            return [];
        }

        $parameters = array_filter(array_map(
            static fn(GroupMetadata $group): array => $group->getParameters(),
            $this->metadata->getGroupChain(),
        ));
        array_unshift($parameters, $this->metadata->getParameters());

        return array_filter(array_merge(...$parameters));
    }
}
