<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Mapping\Annotation;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
final class Router
{
    public function __construct()
    {
        @trigger_error('Router annotation is deprecated as it is not needed any more.', \E_USER_DEPRECATED);
    }
}
