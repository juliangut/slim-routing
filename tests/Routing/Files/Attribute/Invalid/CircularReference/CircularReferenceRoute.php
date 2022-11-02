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

namespace Jgut\Slim\Routing\Tests\Files\Attribute\Invalid\CircularReference;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example circular reference route.
 */
#[JSR\Router]
#[JSR\Group(parent: CircularReferenceRoute::class)]
class CircularReferenceRoute
{
    #[JSR\Route(pattern: 'circular')]
    public function actionCircular(): void
    {
    }
}
