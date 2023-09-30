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

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute;

use Jgut\Slim\Routing\Mapping\Attribute\Group;
use Jgut\Slim\Routing\Mapping\Attribute\Middleware;
use Jgut\Slim\Routing\Mapping\Attribute\Route;

/**
 * Example grouped route.
 */
#[Group(
    pattern: '/grouped/{section}',
    placeholders: ['section' => '[A-Za-z]+'],
)]
#[Middleware('groupedMiddleware')]
class GroupedRoute
{
    #[Route(
        pattern: '/two/{id}',
        arguments: ['scope' => 'protected'],
    )]
    #[Middleware('twoMiddleware')]
    public function actionTwo(): void {}

    #[Route(
        pattern: '/three/{id}',
        xmlHttpRequest: true,
        priority: 10,
        placeholders: ['id' => '\d+'],
    )]
    public function actionThree(): void {}
}
