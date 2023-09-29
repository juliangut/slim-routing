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

namespace Jgut\Slim\Routing\Mapping\Attribute;

trait PathTrait
{
    /**
     * @var array<string, string>
     */
    protected array $placeholders = [];

    /**
     * @var array<string, string>
     */
    protected array $parameters = [];

    /**
     * @param array<string, string>|null $placeholders
     * @param array<string, string>|null $parameters
     */
    public function __construct(
        protected ?string $pattern = null,
        ?array $placeholders = [],
        ?array $parameters = [],
    ) {
        $this->placeholders = $placeholders ?? [];
        $this->parameters = $parameters ?? [];
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @return array<string, string>
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
