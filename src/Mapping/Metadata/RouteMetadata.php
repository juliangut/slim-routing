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
 * Route metadata.
 */
class RouteMetadata extends AbstractMetadata
{
    /**
     * Route name.
     *
     * @var string
     */
    protected $name;

    /**
     * Parent group metadata.
     *
     * @var GroupMetadata
     */
    protected $group;

    /**
     * Parent's group chain.
     *
     * @var GroupMetadata[]
     */
    protected $groupChain;

    /**
     * Route methods.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Route invokable.
     *
     * @var callable
     */
    protected $invokable;

    /**
     * Route load priority.
     *
     * @var int
     */
    protected $priority = 0;

    /**
     * Get route name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set route name.
     *
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get parent group.
     *
     * @return GroupMetadata|null
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set parent group.
     *
     * @param GroupMetadata $group
     *
     * @return static
     */
    public function setGroup(GroupMetadata $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get parent's group chain.
     *
     * @throws \RuntimeException
     *
     * @return GroupMetadata[]
     */
    public function getGroupChain(): array
    {
        if ($this->groupChain === null) {
            $groupChain = [];

            if ($this->group instanceof GroupMetadata) {
                $parent = $this->group;
                while ($parent instanceof GroupMetadata) {
                    if (in_array($parent, $groupChain, true)) {
                        throw new \RuntimeException('Circular group reference detected');
                    }

                    array_unshift($groupChain, $parent);

                    $parent = $parent->getParent();
                }
            }

            $this->groupChain = $groupChain;
        }

        return $this->groupChain;
    }

    /**
     * Get route methods.
     *
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Set route methods.
     *
     * @param array $methods
     *
     * @throws \UnexpectedValueException
     *
     * @return static
     */
    public function setMethods(array $methods): self
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Get route invokable.
     *
     * @return callable
     */
    public function getInvokable()
    {
        return $this->invokable;
    }

    /**
     * Set route invokable.
     *
     * @param callable $invokable
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function setInvokable($invokable): self
    {
        if (!is_string($invokable) && !is_array($invokable) && !is_callable($invokable)) {
            throw new \InvalidArgumentException('Route invokable does not seem to be supported by Slim router');
        }

        $this->invokable = $invokable;

        return $this;
    }

    /**
     * Get route load priority.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Set route load priority.
     *
     * @param int $priority
     *
     * @return static
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
