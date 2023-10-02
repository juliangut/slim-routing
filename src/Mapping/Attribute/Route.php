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

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    /**
     * @var list<string>
     */
    private array $methods;

    /**
     * @param non-empty-string|list<non-empty-string>|null $methods
     */
    public function __construct(
        private ?string $name = null,
        string|array|null $methods = null,
        private ?string $pattern = null,
        /**
         * @var array<string, string>
         */  private array $placeholders = [],
        /**
         * @var array<string, string>
         */  private array $arguments = [],
        protected bool $xmlHttpRequest = false,
        protected int $priority = 0,
    ) {
        if ($methods === null) {
            $methods = ['GET'];
        } elseif (\is_string($methods)) {
            $methods = [$methods];
        }
        $this->methods = $methods;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return list<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
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

    public function isXmlHttpRequest(): bool
    {
        return $this->xmlHttpRequest;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
