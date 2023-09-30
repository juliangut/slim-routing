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
    protected array $middlewares = [];

    /**
     * @return list<class-string<MiddlewareInterface>>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param list<class-string<MiddlewareInterface>> $middlewareList
     *
     * @throws AnnotationException
     */
    public function setMiddlewares(array $middlewareList): static
    {
        $this->middlewares = $middlewareList;

        return $this;
    }
}
