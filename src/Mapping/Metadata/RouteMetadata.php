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
     * Parameters transformer.
     *
     * @var string
     */
    protected $transformer;

    /**
     * Route methods.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * XmlHttpRequest constraint.
     *
     * @var bool
     */
    protected $xmlHttpRequest = false;

    /**
     * Route invocable.
     *
     * @var callable|mixed[]|string
     */
    protected $invocable;

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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set route name.
     *
     * @param string $name
     *
     * @return self
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
    public function getGroup(): ?GroupMetadata
    {
        return $this->group;
    }

    /**
     * Set parent group.
     *
     * @param GroupMetadata $group
     *
     * @return self
     */
    public function setGroup(GroupMetadata $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get parent's group chain.
     *
     * @throws MetadataException
     *
     * @return GroupMetadata[]
     */
    public function getGroupChain(): array
    {
        if ($this->groupChain === null) {
            $groupChain = [];

            $parent = $this->group;
            while ($parent instanceof GroupMetadata) {
                if (\in_array($parent, $groupChain, true)) {
                    throw new MetadataException('Circular group reference detected');
                }

                \array_unshift($groupChain, $parent);

                $parent = $parent->getParent();
            }

            $this->groupChain = $groupChain;
        }

        return $this->groupChain;
    }

    /**
     * Get parameters transformer.
     *
     * @return string|null
     */
    public function getTransformer(): ?string
    {
        return $this->transformer;
    }

    /**
     * Set parameters transformer.
     *
     * @param string $transformer
     *
     * @return self
     */
    public function setTransformer(string $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Get route methods.
     *
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Set route methods.
     *
     * @param string[] $methods
     *
     * @return self
     */
    public function setMethods(array $methods): self
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Get XmlHttpRequest constraint.
     *
     * @return bool
     */
    public function isXmlHttpRequest(): bool
    {
        return $this->xmlHttpRequest;
    }

    /**
     * set XmlHttpRequest constraint.
     *
     * @param bool $xmlHttpRequest
     *
     * @return self
     */
    public function setXmlHttpRequest(bool $xmlHttpRequest): self
    {
        $this->xmlHttpRequest = $xmlHttpRequest;

        return $this;
    }

    /**
     * Get route invocable.
     *
     * @return callable|mixed[]|string
     */
    public function getInvocable()
    {
        return $this->invocable;
    }

    /**
     * Set route invocable.
     *
     * @param mixed $invocable
     *
     * @throws MetadataException
     *
     * @return self
     */
    public function setInvocable($invocable): self
    {
        if (!\is_string($invocable) && !\is_array($invocable) && !\is_callable($invocable)) {
            throw new MetadataException('Route invocable does not seem to be supported by Slim router');
        }

        $this->invocable = $invocable;

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
     * @return self
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
