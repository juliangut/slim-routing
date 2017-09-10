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

/**
 * Route naming interface.
 */
interface NamingInterface
{
    /**
     * Combine name parts.
     *
     * @param string[] $nameParts
     *
     * @return string
     */
    public function combine(array $nameParts): string;
}
