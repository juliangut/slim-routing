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
    private ?GroupMetadata $group = null;

    /**
     * @var list<GroupMetadata>|null
     */
    private ?array $groupChain = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $name = null;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $methods = ['GET'];

    private bool $xmlHttpRequest = false;

    private int $priority = 0;

    public function __construct(
        /**
         * @var string|callable(): mixed
         */
        private $invokable,
    ) {}

    /**
     * @return string|callable(): mixed
     */
    public function getInvokable()
    {
        return $this->invokable;
    }

    /**
     * @return non-empty-string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @throws MetadataException
     */
    public function setName(string $name): self
    {
        if (str_contains(trim($name), ' ')) {
            throw new MetadataException('Route name must not contain spaces.');
        }

        if (trim($name) === '') {
            throw new MetadataException('Route name can not be an empty string.');
        }

        $this->name = trim($name);

        return $this;
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
     * @return non-empty-list<non-empty-string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array<string> $methods
     *
     * @throws MetadataException
     */
    public function setMethods(array $methods): self
    {
        /** @var list<non-empty-string> $methodList */
        $methodList = [];
        foreach ($methods as $method) {
            if (str_contains(trim($method), ' ')) {
                throw new MetadataException('Route method must not contain spaces.');
            }

            if (trim($method) === '') {
                throw new MetadataException('Route method can not be an empty string.');
            }

            $methodList[] = mb_strtoupper(trim($method));
        }

        $methodList = array_filter($methodList, 'strlen');

        if (\count($methodList) === 0) {
            throw new MetadataException('Route methods can not be empty.');
        }

        if (\count($methodList) > 1 && \in_array('ANY', $methodList, true)) {
            throw new MetadataException('Route method "ANY" cannot be defined with other methods.');
        }

        $this->methods = array_values($methodList);

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
