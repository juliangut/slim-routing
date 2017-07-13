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

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlReader;

/**
 * Yaml file routing loader.
 */
class YamlLoader extends AbstractArrayLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return '{yml,yaml}';
    }

    /**
     * {@inheritdoc}
     */
    protected function loadFile(string $file): array
    {
        try {
            $routingData = YamlReader::parse(file_get_contents($file));
        // @codeCoverageIgnoreStart
        } catch (ParseException $exception) {
            throw new \RuntimeException(
                printf('Unable to parse the YAML file %s: %s', $file, $exception->getMessage())
            );
        }
        // @codeCoverageIgnoreEnd

        if (!is_array($routingData)) {
            throw new \RuntimeException(sprintf('Routing file %s should return an array', $file));
        }

        return $routingData;
    }
}
