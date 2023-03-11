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

namespace Jgut\Slim\Routing\Tests\Stubs;

use Jgut\Slim\Routing\Transformer\AbstractTransformer;

class AbstractTransformerStub extends AbstractTransformer
{
    protected $transformed;

    public function __construct($transformed)
    {
        $this->transformed = $transformed;
    }

    protected function transformParameter(string $parameter, string $type)
    {
        return $this->transformed;
    }
}
