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

/**
 * @internal
 */
class AbstractTransformerStub extends AbstractTransformer
{
    public function __construct(
        protected $transformed,
    ) {}

    protected function supportsTransform(string $type): bool
    {
        return true;
    }

    protected function transformParameter(string $parameter, string $type): mixed
    {
        return $this->transformed;
    }
}
