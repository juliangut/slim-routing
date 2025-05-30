<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Mapping\Annotation;

use Jgut\Mapping\Annotation\AbstractAnnotation;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
final class Group extends AbstractAnnotation
{
    use PathTrait;
    use TransformerTrait;
    use MiddlewareTrait;
    use ArgumentTrait;

    private ?string $parent = null;

    private ?string $prefix = null;

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function setParent(string $parent): self
    {
        $this->parent = mb_trim($parent, '\\');

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }
}
