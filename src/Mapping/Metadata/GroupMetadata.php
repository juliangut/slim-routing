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

use Jgut\Mapping\Exception\MetadataException;

final class GroupMetadata extends AbstractMetadata
{
    protected ?self $parent = null;

    /**
     * @var non-empty-string|null
     */
    protected ?string $prefix = null;

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return non-empty-string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): self
    {
        if (str_contains($prefix, ' ')) {
            throw new MetadataException('Group prefix must not contain spaces.');
        }

        if (trim($prefix) === '') {
            throw new MetadataException('Group prefix can not be an empty string.');
        }

        $this->prefix = trim($prefix);

        return $this;
    }
}
