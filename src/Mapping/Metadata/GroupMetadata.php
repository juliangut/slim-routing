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

class GroupMetadata extends AbstractMetadata
{
    protected ?self $parent = null;

    protected ?string $prefix = null;

    /**
     * Get parent group.
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @return static
     */
    public function setParent(self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @return static
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }
}
