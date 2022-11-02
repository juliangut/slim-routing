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

use Jgut\Mapping\Exception\AnnotationException;

/**
 * Route annotation.
 *
 * @Annotation
 *
 * @Target({"METHOD"})
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
    use PathTrait;
    use ArgumentTrait;
    use MiddlewareTrait;

    public function __construct(
        ?string $name = null,
        ?string $transformer = null,
        string|array|null $methods = ['GET'],
        bool $xmlHttpRequest = false,
        int $priority = 0,
        string $pattern = '',
        array $placeholders = [],
        array $parameters = [],
        array $middleware = [],
        array $arguments = []
    ) {
        $params = array_filter(array_keys(get_defined_vars()), fn ($param) => property_exists($this, $param));
        // shut up phpmd
        \func_get_args();

        foreach ($params as $param) {
            if ($this->$param !== $$param) {
                $this->{'set' . ucfirst($param)}($$param);
            }
        }
    }

    /**
     * Route name.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * Parameters transformer.
     *
     * @var string|null
     */
    protected ?string $transformer = null;

    /**
     * Route methods.
     *
     * @var string[]
     */
    protected array $methods = ['GET'];

    /**
     * XmlHttpRequest constraint.
     *
     * @var bool
     */
    protected bool $xmlHttpRequest = false;

    /**
     * Route load priority.
     *
     * @var int
     */
    protected int $priority = 0;

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
        if (str_contains(trim($name), ' ')) {
            throw new AnnotationException('Route name must not contain spaces');
        }

        if (trim($name) === '') {
            throw new AnnotationException('Route name can not be empty');
        }

        $this->name = trim($name);

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
     * @param string|null $transformer
     *
     * @return self
     */
    public function setTransformer(?string $transformer): self
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
     * @param string[]|mixed $methods
     *
     * @throws AnnotationException
     *
     * @return self
     */
    public function setMethods(string|array $methods): self
    {
        $this->methods = [];

        if (!\is_array($methods)) {
            $methods = [$methods];
        }

        foreach (array_filter($methods) as $method) {
            if (!\is_string($method)) {
                throw new AnnotationException(
                    sprintf('Route annotation methods must be strings. "%s" given', \gettype($method))
                );
            }

            $this->methods[] = strtoupper(trim($method));
        }

        $this->methods = array_unique(array_filter($this->methods, function ($method): bool {
            return \strlen($method) > 0;
        }));

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
