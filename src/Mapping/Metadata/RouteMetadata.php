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

final class RouteMetadata extends AbstractMetadata
{
    protected ?GroupMetadata $group = null;

    /**
     * @var list<GroupMetadata>|null
     */
    protected ?array $groupChain = null;

    /**
     * @var list<string>
     */
    protected array $methods = [];

    protected bool $xmlHttpRequest = false;

    protected int $priority = 0;

    /**
     * @throws MetadataException
     */
    public function __construct(
        /**
         * @var string|callable(): mixed
         */
        protected $invokable,
        protected ?string $name,
    ) {}

    /**
     * @return string|callable(): mixed
     */
    public function getInvokable()
    {
        return $this->invokable;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getGroup(): ?GroupMetadata
    {
        return $this->group;
    }

    public function setGroup(GroupMetadata $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @throws MetadataException
     *
     * @return list<GroupMetadata>
     */
    public function getGroupChain(): array
    {
        if ($this->groupChain === null) {
            $groupChain = [];

            $parent = $this->group;
            while ($parent instanceof GroupMetadata) {
                if (\in_array($parent, $groupChain, true)) {
                    throw new MetadataException('Circular group reference detected.');
                }

                array_unshift($groupChain, $parent);

                $parent = $parent->getParent();
            }

            $this->groupChain = $groupChain;
        }

        return $this->groupChain;
    }

    /**
     * @return list<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param list<string> $methods
     */
    public function setMethods(array $methods): self
    {
        $this->methods = $methods;

        return $this;
    }

    public function isXmlHttpRequest(): bool
    {
        return $this->xmlHttpRequest;
    }

    public function setXmlHttpRequest(bool $xmlHttpRequest): self
    {
        $this->xmlHttpRequest = $xmlHttpRequest;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
