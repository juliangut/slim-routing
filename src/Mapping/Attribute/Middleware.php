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

namespace Jgut\Slim\Routing\Mapping\Attribute;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Middleware
{
    /**
     * @param class-string<MiddlewareInterface> $middleware
     */
    public function __construct(
        protected string $middleware,
    ) {}

    /**
     * @return class-string<MiddlewareInterface>
     */
    public function getMiddleware(): string
    {
        return $this->middleware;
    }
}
