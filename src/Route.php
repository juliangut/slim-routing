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

namespace Jgut\Slim\Routing;

/**
 * Routing route.
 */
class Route
{
    /**
     * Route name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Route load priority.
     *
     * @var int
     */
    protected $priority = 0;

    /**
     * Route methods.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Get route pattern.
     *
     * @var string
     */
    protected $pattern = '';

    /**
     * Route placeholders regex.
     *
     * @var string[]
     */
    protected $placeholders = [];

    /**
     * Middleware list.
     *
     * @var string[]
     */
    protected $middleware = [];

    /**
     * Route invokable.
     *
     * @var mixed
     */
    protected $invokable;

    /**
     * Get route name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set route name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

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
     * @return $this
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;

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
     * @param array $methods
     *
     * @return $this
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Get route path.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Set route path.
     *
     * @param string $pattern
     *
     * @return $this
     */
    public function setPattern(string $pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get route parameters restrictions.
     *
     * @return array
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * Set route parameters restrictions.
     *
     * @param array $placeholders
     *
     * @return $this
     */
    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    /**
     * Get middleware.
     *
     * @return string[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Set middleware.
     *
     * @param string[] $middleware
     *
     * @return $this
     */
    public function setMiddleware(array $middleware)
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Get route invokable.
     *
     * @return mixed
     */
    public function getInvokable()
    {
        return $this->invokable;
    }

    /**
     * Set route invokable.
     *
     * @param mixed $invokable
     *
     * @return $this
     */
    public function setInvokable($invokable)
    {
        $this->invokable = $invokable;

        return $this;
    }
}
