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

/**
 * Parameter transformer interface.
 */
interface ParameterTransformer
{
    /**
     * Transform parameters.
     *
     * @param array $parameters
     * @param array $definitions
     *
     * @return array
     */
    public function transform(array $parameters, array $definitions): array;
}
