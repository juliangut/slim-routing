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
 * Abstract metadata stub.
 */
class AbstractTransformerStub extends AbstractTransformer
{
    /**
     * @var mixed
     */
    protected $transformed;

    /**
     * AbstractTransformerStub constructor.
     *
     * @param mixed $transformed
     */
    public function __construct($transformed)
    {
        $this->transformed = $transformed;
    }

    /**
     * {@inheritdoc}
     */
    protected function transformParameter(string $parameter, string $type)
    {
        return $this->transformed;
    }
}
