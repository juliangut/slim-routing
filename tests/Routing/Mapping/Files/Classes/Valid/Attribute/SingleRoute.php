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

use Jgut\Slim\Routing\Mapping\Attribute\Middleware;
use Jgut\Slim\Routing\Mapping\Attribute\Route;
use Jgut\Slim\Routing\Mapping\Attribute\Router;

/**
 * Example single route.
 */
#[Router]
class SingleRoute
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    #[Route(
        pattern: '/one/{id}',
        methods: ['GET', 'POST'],
        name: 'one',
        xmlHttpRequest: true,
        priority: -10,
        transformers: ['fake_transformer'],
        placeholders: ['id' => 'numeric'],
        parameters: ['first' => 'value'],
    )]
    #[Middleware('oneMiddleware')]
    public function actionOne(int $id): void {}
}
