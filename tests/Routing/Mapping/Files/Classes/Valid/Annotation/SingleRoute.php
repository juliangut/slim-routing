<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Annotation;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example single route.
 */
class SingleRoute
{
    /**
     * @JSR\Route(
     *     methods={"GET", "POST"},
     *     pattern="/one/{id}",
     *     priority=-10,
     *     placeholders={"id": "numeric"},
     *     transformers="fake_transformer",
     *     middlewares="oneMiddleware",
     *     xmlHttpRequest=true,
     *     parameters={"first": "value"},
     *     name="one"
     * )
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function actionOne(int $id): void {}
}
