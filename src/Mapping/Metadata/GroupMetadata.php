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

/**
 * Group metadata.
 */
class GroupMetadata extends AbstractMetadata
{
    /**
     * Parent group metadata.
     *
     * @var GroupMetadata
     */
    protected $parent;

    /**
     * Route prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Get parent group.
     *
     * @return GroupMetadata|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent group.
     *
     * @param GroupMetadata $parent
     *
     * @return static
     */
    public function setParent(self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get route prefix.
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set route prefix.
     *
     * @param string $prefix
     *
     * @return static
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }
}
