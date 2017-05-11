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
     * Referenced group.
     *
     * @var string
     */
    protected $group = '';

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
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = trim($name);

        return $this;
    }

    /**
     * Get referenced group.
     *
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Set referenced group.
     *
     * @param string $group
     *
     * @return $this
     */
    public function setGroup(string $group)
    {
        $this->group = trim($group);

        return $this;
    }
}
