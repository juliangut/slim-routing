<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Mapping\Annotation;

use Jgut\Mapping\Annotation\AbstractAnnotation;

/**
 * @Annotation
 *
 * @Target({"METHOD"})
 */
final class Route extends AbstractAnnotation
{
    use PathTrait;
    use TransformerTrait;
    use MiddlewareTrait;
    use ArgumentTrait;

    private ?string $name = null;

    /**
     * @var list<non-empty-string>
     */
    private array $methods = ['GET'];

    private bool $xmlHttpRequest = false;

    private int $priority = 0;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get route methods.
     *
     * @return list<non-empty-string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param non-empty-string|list<non-empty-string> $methods
     */
    public function setMethods(string|array $methods): self
    {
        if (\is_string($methods)) {
            $methods = [$methods];
        }

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
