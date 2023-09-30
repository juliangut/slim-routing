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

namespace Jgut\Slim\Routing\Mapping\Annotation;

use Jgut\Mapping\Annotation\AbstractAnnotation;
use Jgut\Mapping\Exception\AnnotationException;

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

    protected ?string $parent = null;

    protected ?string $prefix = null;

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function setParent(string $parent): self
    {
        $this->parent = trim($parent, '\\');

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @throws AnnotationException
     */
    public function setPrefix(string $prefix): self
    {
        if (str_contains(trim($prefix), ' ')) {
            throw new AnnotationException('Group prefixes must not contain spaces.');
        }

        $this->prefix = trim($prefix);

        return $this;
    }
}
