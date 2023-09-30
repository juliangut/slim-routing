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
    public function __construct(
        /**
         * @var class-string<MiddlewareInterface>|MiddlewareInterface
         */
        protected string|MiddlewareInterface $middleware,
    ) {}

    /**
     * @return class-string<MiddlewareInterface>|MiddlewareInterface
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}
