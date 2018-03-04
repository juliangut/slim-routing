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

namespace Jgut\Slim\Routing\Mapping\Annotation;

/**
 * Path annotation trait.
 */
trait PathTrait
{
    /**
     * Pattern path.
     *
     * @var string
     */
    protected $pattern;

    /**
     * Pattern path placeholders regex.
     *
     * @var array
     */
    protected $placeholders = [];

    /**
     * Pattern parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Get pattern path.
     *
     * @return string|null
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set pattern path.
     *
     * @param string $pattern
     *
     * @return static
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern placeholders regex.
     *
     * @return array
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * Set pattern placeholders regex.
     *
     * @param array $placeholders
     *
     * @return static
     */
    public function setPlaceholders(array $placeholders): self
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * Get parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set parameters.
     *
     * @param array $parameters
     *
     * @return static
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }
}
