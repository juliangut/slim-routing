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
     * @var array<string, string>
     */
    protected array $placeholders = [];

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): static
    {
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
}
