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
use Jgut\Mapping\Exception\AttributeException;

#[Attribute(Attribute::TARGET_CLASS)]
final class Group
{
    use PathTrait;
    use ArgumentTrait;

    protected ?string $prefix = null;

    protected ?string $parent = null;

    /**
     * @param array<string, string>|null $placeholders
     * @param array<string, string>|null $arguments
     */
    public function __construct(
        ?string $prefix = null,
        ?string $parent = null,
        ?string $pattern = null,
        ?array $placeholders = [],
        ?array $arguments = [],
    ) {
        if ($prefix !== null) {
            $this->setPrefix($prefix);
        }
        if ($parent !== null) {
            $this->setParent($parent);
        }
        if ($pattern !== null) {
            $this->setPattern($pattern);
        }
        if ($placeholders !== null) {
            $this->setPlaceholders($placeholders);
        }
        if ($arguments !== null) {
            $this->setArguments($arguments);
        }
    }

    /**
     * @throws AttributeException
     */
    protected function setPrefix(string $prefix): void
    {
        if (str_contains(trim($prefix), ' ')) {
            throw new AttributeException('Group prefixes must not contain spaces.');
        }

        $this->prefix = $prefix;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    protected function setParent(string $parent): void
    {
        $this->parent = trim($parent, '\\');
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }
}
