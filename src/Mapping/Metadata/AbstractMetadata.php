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
    public function getPattern()
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
     * @return static
     */
    public function setPattern(string $pattern): self
    {
        if (\trim($pattern) === '') {
            throw new MetadataException(\sprintf('Pattern can not be empty'));
        }

        $pattern = \trim($pattern, ' /');

        if (\preg_match('/\{([a-zA-Z_][a-zA-Z0-9_-]*):([^}]+)?\}/', $pattern, $matches)) {
            throw new MetadataException(
                \sprintf('Placeholder matching "%s" must be defined on placeholders parameter', $matches[2])
            );
        }

        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get parameters restrictions.
     *
     * @return array
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
     * @return static
     */
    public function setPlaceholders(array $placeholders): self
    {
        $this->placeholders = $placeholders;

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
     * @return static
     */
    public function setMiddleware(array $middleware): self
    {
        $this->middleware = $middleware;

        return $this;
    }
}
