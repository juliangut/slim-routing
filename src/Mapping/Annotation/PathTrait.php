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
    protected ?string $pattern = null;

    /**
     * @var array<string>
     */
    protected array $placeholders = [];

    /**
     * @var array<string, string>
     */
    protected array $parameters = [];

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * @param array<string> $placeholders
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
}
