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
 * Middleware annotation trait.
 */
trait MiddlewareTrait
{
    /**
     * Middleware list.
     *
     * @var string[]
     */
    protected $middleware = [];

    /**
     * Get middleware list.
     *
     * @return string[]
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Set middleware list.
     *
     * @param mixed $middlewareList
     *
     * @throws AnnotationException
     *
     * @return self
     */
    public function setMiddleware($middlewareList): self
    {
        $this->middleware = [];

        if (!\is_array($middlewareList)) {
            $middlewareList = [$middlewareList];
        }

        /** @var array $middlewareList */
        foreach ($middlewareList as $middleware) {
            if (!\is_string($middleware)) {
                throw new AnnotationException(
                    \sprintf('Route annotation middleware must be strings. "%s" given', \gettype($middleware))
                );
            }

            $this->middleware[] = $middleware;
        }

        return $this;
    }
}
