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

namespace Jgut\Slim\Routing\Tests\Files\Attribute\Invalid\NoRoutes;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example no routes route.
 */
#[JSR\Router]
class NoRoutesRoute
{
}
