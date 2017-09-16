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

namespace Jgut\Slim\Routing\Mapping\Loader;

/**
 * PHP file mapping loader.
 */
class PhpLoader extends AbstractFileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return 'php';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function loadFile(string $file): array
    {
        $mappingData = require $file;

        if (!is_array($mappingData)) {
            throw new \RuntimeException(sprintf('Routing file %s should return an array', $file));
        }

        return $mappingData;
    }
}
