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

namespace Jgut\Slim\Routing\Loader;

/**
 * JSON file routing loader.
 */
class JsonLoader extends AbstractArrayLoader
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
     */
    protected function loadFile(string $file): array
    {
        $routingData = json_decode(file_get_contents($file), true);

        if (!is_array($routingData)) {
            throw new \RuntimeException(sprintf('Routing file %s should return an array', $file));
        }

        return $routingData;
    }
}
