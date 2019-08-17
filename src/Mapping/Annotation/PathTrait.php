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
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * Set pattern path.
     *
     * @param string $pattern
     *
     * @return self
     */
    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern placeholders regex.
     *
     * @return string[]
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * Set pattern placeholders regex.
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
     * Get parameters.
     *
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set parameters.
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
}
