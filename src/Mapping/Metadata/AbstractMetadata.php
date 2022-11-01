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

/**
 * Abstract metadata.
 */
abstract class AbstractMetadata implements MetadataInterface
{
    /**
     * Path pattern.
     *
     * @var string
     */
    protected $pattern;

    /**
     * Placeholders regex.
     *
     * @var string[]
     */
    protected $placeholders = [];

    /**
     * Pattern parameters.
     *
     * @var mixed[]
     */
    protected $parameters = [];

    /**
     * Arguments.
     *
     * @var mixed[]
     */
    protected $arguments = [];

    /**
     * Middleware list.
     *
     * @var callable[]|string[]
     */
    protected $middleware = [];

    /**
     * Get path pattern.
     *
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * Set path pattern.
     *
     * @param string $pattern
     *
     * @throws MetadataException
     *
     * @return self
     */
    public function setPattern(string $pattern): self
    {
        if (trim($pattern) === '') {
            throw new MetadataException(sprintf('Pattern can not be empty'));
        }

        $pattern = trim($pattern, ' /');

        if ((bool) preg_match('/{([a-zA-Z_][a-zA-Z0-9_-]*):([^}]+)?}/', $pattern, $matches) !== false) {
            throw new MetadataException(
                sprintf('Placeholder matching "%s" must be defined on placeholders parameter', $matches[2])
            );
        }

        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get parameters restrictions.
     *
     * @return string[]
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * Set parameters restrictions.
     *
     * @param string[] $placeholders
     *
     * @return self
     */
    public function setPlaceholders(array $placeholders): self
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * Get pattern parameters.
     *
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set pattern parameters.
     *
     * @param mixed[] $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get arguments.
     *
     * @return mixed[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Set arguments.
     *
     * @param mixed[] $attributes
     *
     * @return self
     */
    public function setArguments(array $attributes): self
    {
        $this->arguments = $attributes;

        return $this;
    }

    /**
     * Get middleware.
     *
     * @return callable[]|string[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Set middleware.
     *
     * @param callable[]|string[] $middleware
     *
     * @return self
     */
    public function setMiddleware(array $middleware): self
    {
        $this->middleware = array_map(
            function (string $middleware): string {
                return ltrim($middleware, '\\');
            },
            $middleware
        );

        return $this;
    }
}
