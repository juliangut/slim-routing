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
 * Routing source factory.
 */
class SourceFactory
{
    public static function getSource($source)
    {
        if ($source instanceof SourceInterface) {
            return $source;
        }

        if (is_string($source) && is_dir($source)) {
            return new AnnotationSource($source);
        }

        if (is_string($source) && is_file($source)) {
            return self::getSourceFromFile($source);
        }

        throw new \InvalidArgumentException(sprintf(
            '"%s" routing source. Must be a SourceInterface, a directory or a path',
            is_object($source) ? get_class($source) : $source
        ));
    }

    protected static function getSourceFromFile(string $file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'php':
                return new PhpSource($file);
                break;

            case 'yml':
            case 'yaml':
                return new YamlSource($file);
                break;
        }

        throw new \RuntimeException(sprintf('Unknown "%s" extension', $extension));
    }
}
