<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Annotation;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example dependent route.
 *
 * @JSR\Group(
 *     parent="\Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Annotation\AbstractRoute",
 *     prefix="dependent",
 *     pattern="/dependent",
 *     middlewares={"dependentMiddleware"}
 * )
 */
class DependentRoute
{
    /**
     * @JSR\Route(
     *     name="four",
     *     methods="GET",
     *     pattern="/four",
     *     middlewares={"fourMiddleware"}
     * )
     */
    public function actionFour(): void {}
}
