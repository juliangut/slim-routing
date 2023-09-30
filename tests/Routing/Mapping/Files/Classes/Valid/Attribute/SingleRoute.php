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
use Jgut\Slim\Routing\Mapping\Attribute\Transformer;

/**
 * Example single route.
 */
class SingleRoute
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    #[Route(
        name: 'one',
        methods: ['GET', 'POST'],
        pattern: '/one/{id}',
        placeholders: ['id' => 'numeric'],
        xmlHttpRequest: true,
        priority: -10,
    )]
    #[Middleware('oneMiddleware')]
    #[Transformer(transformer: 'fake_transformer', parameters: ['first' => 'value'])]
    public function actionOne(int $id): void {}
}
