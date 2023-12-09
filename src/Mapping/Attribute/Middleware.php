<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Mapping\Attribute;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Middleware
{
    public function __construct(
        /**
         * @var class-string<MiddlewareInterface>
         */
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
