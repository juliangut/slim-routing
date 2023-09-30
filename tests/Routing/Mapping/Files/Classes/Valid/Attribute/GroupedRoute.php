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
use Jgut\Slim\Routing\Mapping\Attribute\Transformer;

/**
 * Example grouped route.
 */
#[Group(
    pattern: '/grouped/{section}',
    placeholders: ['section' => '[A-Za-z]+'],
)]
#[Middleware('group-middleware')]
#[Transformer(transformer: 'group-transformer', parameters: ['section' => 'string'])]
class GroupedRoute
{
    #[Route(
        pattern: '/two/{id}',
        arguments: ['scope' => 'protected'],
    )]
    #[Middleware('twoMiddleware')]
    #[Transformer(transformer: 'route-transformer', parameters: ['id' => 'int'])]
    public function actionTwo(): void {}

    #[Route(
        pattern: '/three/{id}',
        placeholders: ['id' => '\d+'],
        xmlHttpRequest: true,
        priority: 10,
    )]
    public function actionThree(): void {}
}
