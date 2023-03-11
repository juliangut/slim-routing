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
class Middleware
{
    /**
     * @var class-string<MiddlewareInterface>
     */
    protected string $middleware;

    /**
     * @param class-string<MiddlewareInterface> $middleware
     */
    public function __construct(string $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @return class-string<MiddlewareInterface>
     */
    public function getMiddleware(): string
    {
        return $this->middleware;
    }
}
