<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Annotation\CircularReference;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example circular reference route.
 *
 * @JSR\Group(
 * parent="Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Annotation\CircularReference\CircularReferenceRoute"
 * )
 */
class CircularReferenceRoute
{
    /**
     * @JSR\Route(
     *     pattern="/circular"
     * )
     */
    public function actionCircular(): void {}
}
