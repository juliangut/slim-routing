<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Naming;

final class Dot implements Strategy
{
    public function combine(array $nameParts): string
    {
        return implode('.', $nameParts);
    }
}
