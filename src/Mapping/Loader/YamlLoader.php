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

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlReader;

/**
 * YAML file routing loader.
 */
class YamlLoader extends AbstractFileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function getExtension(): string
    {
        return '(yml|yaml)';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function loadFile(string $file): array
    {
        try {
            $mappingData = YamlReader::parse(file_get_contents($file));
            // @codeCoverageIgnoreStart
        } catch (ParseException $exception) {
            throw new \RuntimeException(
                printf('Unable to parse the YAML file %s: %s', $file, $exception->getMessage())
            );
        }
        // @codeCoverageIgnoreEnd

        if (!is_array($mappingData)) {
            throw new \RuntimeException(sprintf('Routing file %s should return an array', $file));
        }

        return $mappingData;
    }
}
