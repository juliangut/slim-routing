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
    /**
     * Get source.
     *
     * @param SourceInterface|string $source
     *
     * @throws \InvalidArgumentException
     *
     * @return SourceInterface
     */
    public static function getSource($source): SourceInterface
    {
        if ($source instanceof SourceInterface) {
            return $source;
        }

        if (is_string($source)) {
            if (is_dir($source)) {
                return new AnnotationSource($source);
            }

            if (is_file($source)) {
                return self::getFileSource($source);
            }
        }

        throw new \InvalidArgumentException(sprintf(
            '"%s" routing source. Must be a SourceInterface, a directory or a file path',
            is_object($source) ? get_class($source) : $source
        ));
    }

    /**
     * Get source from file.
     *
     * @param string $file
     *
     * @throws \RuntimeException
     *
     * @return SourceInterface
     */
    protected static function getFileSource(string $file): SourceInterface
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'php':
                return new PhpSource($file);

            case 'yml':
            case 'yaml':
                return new YamlSource($file);
        }

        throw new \RuntimeException(sprintf('Unknown "%s" extension', $extension));
    }
}
