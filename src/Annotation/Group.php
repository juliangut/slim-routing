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

namespace Jgut\Slim\Routing\Annotation;

/**
 * Router annotation.
 *
 * @Annotation
 * @Target("CLASS")
 */
class Group extends AbstractAnnotation
{
    use PathTrait;
    use MiddlewareTrait;

    /**
     * Group name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Parent group.
     *
     * @var string
     */
    protected $parent = '';

    /**
     * Group name prefix.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Router constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->seedParameters($parameters);
    }

    /**
     * Get group name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set group name.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setName(string $name)
    {
        if (strpos(trim($name), ' ') !== false) {
            throw new \InvalidArgumentException(sprintf('Group names must not contain spaces'));
        }

        $this->name = trim($name);

        return $this;
    }

    /**
     * Get parent group.
     *
     * @return string
     */
    public function getParent(): string
    {
        return $this->parent;
    }

    /**
     * Set parent group.
     *
     * @param string $parent
     *
     * @return $this
     */
    public function setParent(string $parent)
    {
        $this->parent = trim($parent);

        return $this;
    }

    /**
     * Get group name prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Set group name prefix.
     *
     * @param string $prefix
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setPrefix(string $prefix)
    {
        if (strpos(trim($prefix), ' ') !== false) {
            throw new \InvalidArgumentException(sprintf('Group prefixes must not contain spaces'));
        }

        $this->prefix = trim($prefix);

        return $this;
    }
}
