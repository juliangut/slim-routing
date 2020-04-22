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
 * Router annotation.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Group extends AbstractAnnotation
{
    use PathTrait;
    use ArgumentTrait;
    use MiddlewareTrait;

    /**
     * Parent group.
     *
     * @var string
     */
    protected $parent;

    /**
     * Group name prefix.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Get parent group.
     *
     * @return string|null
     */
    public function getParent(): ?string
    {
        return $this->parent;
    }

    /**
     * Set parent group.
     *
     * @param string $parent
     *
     * @return self
     */
    public function setParent(string $parent): self
    {
        $this->parent = \trim($parent, '\\');

        return $this;
    }

    /**
     * Get group name prefix.
     *
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * Set group name prefix.
     *
     * @param string $prefix
     *
     * @throws AnnotationException
     *
     * @return self
     */
    public function setPrefix(string $prefix): self
    {
        if (\strpos(\trim($prefix), ' ') !== false) {
            throw new AnnotationException(\sprintf('Group prefixes must not contain spaces'));
        }

        $this->prefix = \trim($prefix);

        return $this;
    }
}
