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
use Jgut\Mapping\Exception\AnnotationException;

/**
 * @Annotation
 *
 * @Target({"METHOD"})
 */
final class Route extends AbstractAnnotation
{
    use PathTrait;
    use ArgumentTrait;
    use MiddlewareTrait;

    protected ?string $name = null;

    protected ?string $transformer = null;

    /**
     * @var list<string>
     */
    protected array $methods = ['GET'];

    protected bool $xmlHttpRequest = false;

    protected int $priority = 0;

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @throws AnnotationException
     */
    public function setName(string $name): self
    {
        if (str_contains(trim($name), ' ')) {
            throw new AnnotationException('Route name must not contain spaces.');
        }

        if (trim($name) === '') {
            throw new AnnotationException('Route name can not be empty.');
        }

        $this->name = trim($name);

        return $this;
    }

    public function getTransformer(): ?string
    {
        return $this->transformer;
    }

    public function setTransformer(string $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Get route methods.
     *
     * @return list<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param list<string>|mixed $methods
     *
     * @throws AnnotationException
     */
    public function setMethods($methods): self
    {
        $this->methods = [];

        if (!\is_array($methods)) {
            $methods = [$methods];
        }

        foreach (array_filter($methods) as $method) {
            if (!\is_string($method)) {
                throw new AnnotationException(
                    sprintf('Route annotation methods must be strings. "%s" given.', \gettype($method)),
                );
            }

            $this->methods[] = mb_strtoupper(trim($method));
        }

        $this->methods = array_values(array_unique(array_filter($this->methods, 'strlen')));

        if (\count($this->methods) === 0) {
            throw new AnnotationException('Route annotation methods can not be empty.');
        }

        if (\in_array('ANY', $this->methods, true) && \count($this->methods) > 1) {
            throw new AnnotationException('Route "ANY" method cannot be defined with other methods.');
        }

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
