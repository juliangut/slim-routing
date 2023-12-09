<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Naming;

final class SnakeCase implements Strategy
{
    public function combine(array $nameParts): string
    {
        return implode('_', $nameParts);
    }
}
