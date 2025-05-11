<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Mapping\Metadata;

use Jgut\Mapping\Exception\MetadataException;

final class GroupMetadata extends AbstractMetadata
{
    private ?self $parent = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $prefix = null;

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
