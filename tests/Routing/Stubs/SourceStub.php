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

use Jgut\Slim\Routing\Source\AbstractSource;

/**
 * Abstract source stub.
 */
class SourceStub extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function getLoaderClass(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getCompilerClass(): string
    {
        return '';
    }
}
