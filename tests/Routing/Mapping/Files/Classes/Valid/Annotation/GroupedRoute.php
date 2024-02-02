<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Annotation;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example grouped route.
 *
 * @JSR\Group(
 *     pattern="/grouped/{section}",
 *     placeholders={"section": "[A-Za-z]+"},
 *     parameters={"section": "string"},
 *     transformers={"group-transformer"},
 *     middlewares={"group-middleware"}
 * )
 */
class GroupedRoute
{
    /**
     * @JSR\Route(
     *     pattern="/two/{id}",
     *     arguments={"scope": "protected"},
     *     parameters={"id": "int"},
     *     transformers={"route-transformer"},
     *     middlewares={"twoMiddleware"},
     * )
     */
    public function actionTwo(): void {}

    /**
     * @JSR\Route(
     *     pattern="/three/{id}",
     *     xmlHttpRequest=true,
     *     priority=10,
     *     placeholders={"id":"\d+"}
     * )
     */
    public function actionThree(): void {}
}
