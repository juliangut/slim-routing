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

namespace Jgut\Slim\Routing\Naming;

class CamelCase implements Strategy
{
    public function combine(array $nameParts): string
    {
        return lcfirst(implode('', array_map('ucfirst', $nameParts)));
    }
}
