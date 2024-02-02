<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Transformer;

interface ParameterTransformer
{
    public function supports(string $parameter, string $type): bool;

    public function transform(string $parameter, string $type, mixed $value): mixed;
}
