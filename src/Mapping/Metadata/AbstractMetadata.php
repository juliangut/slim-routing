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

namespace Jgut\Slim\Routing\Mapping\Metadata;

use Jgut\Mapping\Exception\MetadataException;
use Jgut\Mapping\Metadata\MetadataInterface;
use Jgut\Slim\Routing\Transformer\ParameterTransformer;
use Psr\Http\Server\MiddlewareInterface;

abstract class AbstractMetadata implements MetadataInterface
{
    protected ?string $pattern = null;

    /**
     * @var array<string, string>
     */
    protected array $placeholders = [];

    /**
     * @var array<string, string>
     */
    protected array $parameters = [];

    /**
     * @var list<class-string<ParameterTransformer>|ParameterTransformer>
     */
    protected array $transformers = [];

    /**
     * @var array<string, mixed>
     */
    protected array $arguments = [];

    /**
     * @var list<class-string<MiddlewareInterface>|MiddlewareInterface>
     */
    protected array $middlewares = [];

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @throws MetadataException
     */
    public function setPattern(string $pattern): static
    {
        if (trim($pattern) === '') {
            throw new MetadataException('Pattern can not be empty.');
        }

        $pattern = trim($pattern, ' /');

        if ((bool) preg_match('/{([a-zA-Z_][a-zA-Z0-9_-]*):([^}]+)?}/', $pattern, $matches) !== false) {
            throw new MetadataException(
                sprintf('Placeholder matching "%s" must be defined on placeholders parameter.', $matches[2]),
            );
        }

        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * @param array<string, string> $placeholders
     */
    public function setPlaceholders(array $placeholders): static
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, string> $parameters
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return list<class-string<ParameterTransformer>|ParameterTransformer>
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * @param array<mixed> $transformers
     *
     * @throws MetadataException
     */
    public function setTransformers(array $transformers): self
    {
        foreach ($transformers as $transformer) {
            if (!\is_string($transformer) && !$transformer instanceof ParameterTransformer) {
                throw new MetadataException(sprintf(
                    'Transformers must be an array of strings or "%s". "%s" given.',
                    ParameterTransformer::class,
                    \is_object($transformer) ? $transformer::class : \gettype($transformer),
                ));
            }
        }

        /** @var array<string, class-string<ParameterTransformer>|ParameterTransformer> $transformers */
        $this->transformers = array_values($transformers);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function setArguments(array $attributes): static
    {
        $this->arguments = $attributes;

        return $this;
    }

    /**
     * @return list<class-string<MiddlewareInterface>|MiddlewareInterface>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param array<mixed> $middlewares
     *
     * @throws MetadataException
     */
    public function setMiddlewares(array $middlewares): static
    {
        foreach ($middlewares as $middleware) {
            if (!\is_string($middleware) && !$middleware instanceof MiddlewareInterface) {
                throw new MetadataException(sprintf(
                    'Middlewares must be an array of strings or "%s". "%s" given.',
                    MiddlewareInterface::class,
                    \is_object($middleware) ? $middleware::class : \gettype($middleware),
                ));
            }
        }

        /** @var array<string, class-string<MiddlewareInterface>|MiddlewareInterface> $middlewares */
        $this->middlewares = array_values($middlewares);

        return $this;
    }
}
