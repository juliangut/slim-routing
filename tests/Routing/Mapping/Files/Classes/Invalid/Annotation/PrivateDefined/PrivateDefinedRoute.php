<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Invalid\Annotation\PrivateDefined;

use Jgut\Slim\Routing\Mapping\Annotation as JSR;

/**
 * Example private method defined route.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
 */
class PrivateDefinedRoute
{
    /**
     * @JSR\Route(
     *     pattern="/private"
     * )
     */
    private function privateAction(): void {}
}
