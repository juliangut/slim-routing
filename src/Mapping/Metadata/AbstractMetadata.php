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
use Psr\Http\Message\ResponseInterface;
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
     * @var array<string, mixed>
     */
    protected array $arguments = [];

    /**
     * @var array<string|callable(): ResponseInterface|MiddlewareInterface>
     */
    protected array $middleware = [];

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @throws MetadataException
     *
     * @return static
     */
    public function setPattern(string $pattern): self
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
     *
     * @return static
     */
    public function setPlaceholders(array $placeholders): self
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
     *
     * @return static
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

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
     *
     * @return static
     */
    public function setArguments(array $attributes): self
    {
        $this->arguments = $attributes;

        return $this;
    }

    /**
     * @return array<string|callable(): ResponseInterface|MiddlewareInterface>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param array<string|callable(): ResponseInterface|MiddlewareInterface> $middleware
     *
     * @return static
     */
    public function setMiddleware(array $middleware): self
    {
        $this->middleware = array_map(
            static fn(string $middleware): string => ltrim($middleware, '\\'),
            $middleware,
        );

        return $this;
    }
}
