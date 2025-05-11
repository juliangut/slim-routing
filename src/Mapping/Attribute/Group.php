<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Mapping\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Group
{
    public function __construct(
        private ?string $prefix = null,
        private ?string $parent = null,
        private ?string $pattern = null,
        /**
         * @var array<string, string>
         */
        private array $placeholders = [],
        /**
         * @var array<string, string>
         */
        private array $arguments = [],
    ) {}

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getParent(): ?string
    {
        return $this->parent;
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
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
