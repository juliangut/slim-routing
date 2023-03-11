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
use Psr\Http\Message\ResponseInterface;

class RouteMetadata extends AbstractMetadata
{
    protected ?string $name;

    protected ?GroupMetadata $group = null;

    /**
     * @var array<GroupMetadata>
     */
    protected ?array $groupChain = null;

    protected ?string $transformer = null;

    /**
     * @var array<string>
     */
    protected array $methods = [];

    protected bool $xmlHttpRequest = false;

    /**
     * @var string|callable(): ResponseInterface
     */
    protected $invokable;

    protected int $priority = 0;

    /**
     * @phpstan-param string|callable(): ResponseInterface|mixed $invokable
     *
     * @throws MetadataException
     */
    public function __construct($invokable, ?string $name)
    {
        if (!\is_string($invokable) && !\is_array($invokable) && !\is_callable($invokable)) {
            throw new MetadataException('Route invokable does not seem to be supported by Slim router.');
        }

        /** @var callable $invokable */
        $this->invokable = $invokable;
        $this->name = $name;
    }

    /**
     * @return string|callable(): ResponseInterface
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
     * @return array<GroupMetadata>
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

    public function getTransformer(): ?string
    {
        return $this->transformer;
    }

    public function setTransformer(string $transformer): self
    {
        $this->transformer = ltrim($transformer, '\\');

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array<string> $methods
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
