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
 * Route annotation.
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class Route extends AbstractAnnotation
{
    use PathTrait;
    use MiddlewareTrait;

    /**
     * Route name.
     *
     * @var string
     */
    protected $name;

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
    protected $methods = ['GET'];

    /**
     * XmlHttpRequest constraint.
     *
     * @var bool
     */
    protected $xmlHttpRequest = false;

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
     * @throws AnnotationException
     *
     * @return self
     */
    public function setName(string $name): self
    {
        if (\strpos(\trim($name), ' ') !== false) {
            throw new AnnotationException(\sprintf('Route name must not contain spaces'));
        }

        if (\trim($name) === '') {
            throw new AnnotationException(\sprintf('Route name can not be empty'));
        }

        $this->name = \trim($name);

        return $this;
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
     * @param mixed $methods
     *
     * @throws AnnotationException
     *
     * @return self
     */
    public function setMethods($methods): self
    {
        $this->methods = [];

        if (!\is_array($methods)) {
            $methods = [$methods];
        }

        /** @var array $methods */
        foreach (\array_filter($methods) as $method) {
            if (!\is_string($method)) {
                throw new AnnotationException(
                    \sprintf('Route annotation methods must be strings. "%s" given', \gettype($method))
                );
            }

            $this->methods[] = \strtoupper(\trim($method));
        }

        $this->methods = \array_unique(\array_filter($this->methods, 'strlen'));

        if (\count($this->methods) === 0) {
            throw new AnnotationException('Route annotation methods can not be empty');
        }

        if (\in_array('ANY', $this->methods, true) && \count($this->methods) > 1) {
            throw new AnnotationException('Route "ANY" method cannot be defined with other methods');
        }

        return $this;
    }

    /**
     * Is XmlHttpRequest.
     *
     * @return bool
     */
    public function isXmlHttpRequest(): bool
    {
        return $this->xmlHttpRequest;
    }

    /**
     * Set XmlHttpRequest constraint.
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
