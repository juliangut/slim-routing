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
     * Route methods.
     *
     * @var array
     */
    protected $methods = ['GET'];

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
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function setName(string $name): self
    {
        if (strpos(trim($name), ' ') !== false) {
            throw new \InvalidArgumentException(sprintf('Route name must not contain spaces'));
        }

        if (trim($name) === '') {
            throw new \InvalidArgumentException(sprintf('Route name can not be empty'));
        }

        $this->name = trim($name);

        return $this;
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
     * @param array|string $methods
     *
     * @throws \UnexpectedValueException
     *
     * @return static
     */
    public function setMethods($methods): self
    {
        $this->methods = [];

        if (!is_array($methods)) {
            $methods = [$methods];
        }

        /** @var array $methods */
        foreach (array_filter($methods) as $method) {
            if (!is_string($method)) {
                throw new \UnexpectedValueException(
                    sprintf('Route annotation methods must be strings. "%s" given', gettype($method))
                );
            }

            $this->methods[] = strtoupper(trim($method));
        }

        $this->methods = array_unique(array_filter($this->methods, 'strlen'));

        if (count($this->methods) === 0) {
            throw new \UnexpectedValueException('Route annotation methods can not be empty');
        }

        if (in_array('ANY', $this->methods, true) && count($this->methods) > 1) {
            throw new \UnexpectedValueException('Route "ANY" method cannot be defined with other methods');
        }

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
