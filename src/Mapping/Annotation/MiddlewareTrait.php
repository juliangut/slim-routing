<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
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
     * @var list<non-empty-string>
     */
    protected array $middlewares = [];

    /**
     * @return list<non-empty-string>
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param non-empty-string|list<non-empty-string> $middlewareList
     *
     * @throws AnnotationException
     */
    public function setMiddlewares(string|array $middlewareList): static
    {
        if (\is_string($middlewareList)) {
            $middlewareList = [$middlewareList];
        }

        $this->middlewares = $middlewareList;

        return $this;
    }
}
