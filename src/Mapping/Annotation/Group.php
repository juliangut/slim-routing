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
    protected $name;

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
     * Get group name.
     *
     * @return string|null
     */
    public function getName()
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
     * @return self
     */
    public function setName(string $name): Group
    {
        if (strpos(trim($name), ' ') !== false) {
            throw new \InvalidArgumentException(sprintf('Group name must not contain spaces'));
        }

        if (trim($name) === '') {
            throw new \InvalidArgumentException(sprintf('Group name can not be empty'));
        }

        $this->name = trim($name);

        return $this;
    }

    /**
     * Get parent group.
     *
     * @return string|null
     */
    public function getParent()
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
    public function setParent(string $parent): Group
    {
        $this->parent = trim($parent);

        return $this;
    }

    /**
     * Get group name prefix.
     *
     * @return string|null
     */
    public function getPrefix()
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
     * @return self
     */
    public function setPrefix(string $prefix): Group
    {
        if (strpos(trim($prefix), ' ') !== false) {
            throw new \InvalidArgumentException(sprintf('Group prefixes must not contain spaces'));
        }

        $this->prefix = trim($prefix);

        return $this;
    }
}
