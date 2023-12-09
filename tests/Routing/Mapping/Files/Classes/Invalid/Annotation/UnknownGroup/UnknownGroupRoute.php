<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Annotation\UnknownGroup;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example unknown group route.
 *
 * @JSR\Group(
 * parent="unknown"
 * )
 */
class UnknownGroupRoute
{
    /**
     * @JSR\Route(
     *     pattern="/unknown"
     * )
     */
    public function actionUnknown(): void {}
}
