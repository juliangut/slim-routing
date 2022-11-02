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

use Jgut\Mapping\Exception\AnnotationException;

/**
 * Router annotation.
 *
 * @Annotation
 *
 * @Target("CLASS")
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Group
{
    use PathTrait;
    use ArgumentTrait;
    use MiddlewareTrait;

    public function __construct(
        ?string $parent = null,
        ?string $prefix = null,
        ?string $pattern = null,
        array $placeholders = [],
        array $parameters = [],
        array $middleware = [],
        array $arguments = []
    ) {
        $params = array_filter(array_keys(get_defined_vars()), fn ($param) => property_exists($this, $param));
        // shut up phpmd
        \func_get_args();

        foreach ($params as $param) {
            if ($this->$param !== $$param) {
                $this->{'set' . ucfirst($param)}($$param);
            }
        }
    }

    /**
     * Parent group.
     *
     * @var string|null
     */
    protected ?string $parent = null;

    /**
     * Group name prefix.
     *
     * @var string|null
     */
    protected ?string $prefix = null;

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
        $this->parent = trim($parent, '\\');

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
     * @return self
     */
    public function setPrefix(string $prefix): self
    {
        if (str_contains(trim($prefix), ' ')) {
            throw new AnnotationException('Group prefixes must not contain spaces');
        }

        $this->prefix = trim($prefix);

        return $this;
    }
}
