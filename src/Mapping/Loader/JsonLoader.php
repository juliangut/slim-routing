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
 * JSON file mapping loader.
 */
class JsonLoader extends AbstractFileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return 'json';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function loadFile(string $file): array
    {
        $mappingData = json_decode(file_get_contents($file), true);

        if (!is_array($mappingData)) {
            throw new \RuntimeException(sprintf('Routing file %s should return an array', $file));
        }

        return $mappingData;
    }
}
