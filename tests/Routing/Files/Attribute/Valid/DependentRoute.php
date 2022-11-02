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

namespace Jgut\Slim\Routing\Tests\Files\Attribute\Valid;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example dependent route.
 */
#[JSR\Router]
#[JSR\Group(
    parent: '\\Jgut\\Slim\\Routing\\Tests\\Files\\Attribute\\Valid\\AbstractRoute',
    prefix: 'grouped',
    pattern: '/dependent',
    middleware: ['dependentMiddleware']
)]
class DependentRoute
{
    #[JSR\Route(name: 'four', pattern: '/four', middleware: ['fourMiddleware'])]
    public function actionFour(): void
    {
    }
}
