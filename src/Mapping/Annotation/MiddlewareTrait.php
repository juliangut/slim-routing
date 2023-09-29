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
use Psr\Http\Server\MiddlewareInterface;

/**
 * Middleware annotation trait.
 */
trait MiddlewareTrait
{
    /**
     * @var list<class-string<MiddlewareInterface>>
     */
    protected array $middleware = [];

    /**
     * @return list<class-string<MiddlewareInterface>>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param list<class-string<MiddlewareInterface>>|mixed $middlewareList
     *
     * @throws AnnotationException
     */
    public function setMiddleware($middlewareList): static
    {
        $this->middleware = [];

        if (!\is_array($middlewareList)) {
            $middlewareList = [$middlewareList];
        }

        foreach ($middlewareList as $middleware) {
            if (!\is_string($middleware)) {
                throw new AnnotationException(
                    sprintf('Route annotation middleware must be strings. "%s" given.', \gettype($middleware)),
                );
            }

            /** @var class-string<MiddlewareInterface> $middleware */
            $this->middleware[] = $middleware;
        }

        return $this;
    }
}
