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
class Group
{
    use PathTrait {
        PathTrait::__construct as protected pathConstruct;
    }
    use ArgumentTrait {
        ArgumentTrait::__construct as protected argumentConstruct;
    }

    protected ?string $parent = null;

    protected ?string $prefix = null;

    /**
     * @param array<string, string>|null $placeholders
     * @param array<string, string>|null $parameters
     * @param array<string, string>|null $arguments
     */
    public function __construct(
        ?string $parent = null,
        ?string $prefix = null,
        ?string $pattern = null,
        ?array $placeholders = [],
        ?array $parameters = [],
        ?array $arguments = []
    ) {
        if ($parent !== null) {
            $this->setParent($parent);
        }
        if ($prefix !== null) {
            $this->setPrefix($prefix);
        }

        $this->pathConstruct($pattern, $placeholders, $parameters);
        $this->argumentConstruct($arguments);
    }

    protected function setParent(string $parent): void
    {
        $this->parent = trim($parent, '\\');
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    /**
     * @throws AttributeException
     */
    protected function setPrefix(string $prefix): void
    {
        if (mb_strpos(trim($prefix), ' ') !== false) {
            throw new AttributeException('Group prefixes must not contain spaces.');
        }

        $this->prefix = $prefix;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }
}
