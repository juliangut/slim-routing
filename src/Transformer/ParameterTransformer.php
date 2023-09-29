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

namespace Jgut\Slim\Routing\Transformer;

interface ParameterTransformer
{
    public function supports(string $parameter, string $type): bool;

    public function transform(string $parameter, string $type, mixed $value): mixed;
}
