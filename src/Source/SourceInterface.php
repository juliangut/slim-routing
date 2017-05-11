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

namespace Jgut\Slim\Routing\Source;

/**
 * Routing source interface.
 */
interface SourceInterface
{
    /**
     * Get routing paths.
     *
     * @return array
     */
    public function getPaths(): array;

    /**
     * Get loader class.
     *
     * @return string
     */
    public function getLoaderClass(): string;

    /**
     * Get compiler class.
     *
     * @return string
     */
    public function getCompilerClass(): string;
}
