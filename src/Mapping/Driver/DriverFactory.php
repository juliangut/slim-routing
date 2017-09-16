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

namespace Jgut\Slim\Routing\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Jgut\Slim\Routing\Mapping\Loader\AnnotationLoader;
use Jgut\Slim\Routing\Mapping\Loader\JsonLoader;
use Jgut\Slim\Routing\Mapping\Loader\PhpLoader;
use Jgut\Slim\Routing\Mapping\Loader\YamlLoader;

/**
 * Mapping driver factory.
 */
class DriverFactory
{
    /**
     * Get annotation driver.
     *
     * @param AnnotationReader|null $annotationReader
     *
     * @return AnnotationDriver
     */
    public static function getAnnotationDriver(AnnotationReader $annotationReader = null): AnnotationDriver
    {
        return new AnnotationDriver(
            new AnnotationLoader(),
            $annotationReader === null ? new AnnotationReader() : $annotationReader
        );
    }

    /**
     * Get PHP files driver.
     *
     * @return DefinitionFileDriver
     */
    public static function getPhpDriver(): DefinitionFileDriver
    {
        return new DefinitionFileDriver(new PhpLoader());
    }

    /**
     * Get Json files driver.
     *
     * @return DefinitionFileDriver
     */
    public static function getJsonDriver(): DefinitionFileDriver
    {
        return new DefinitionFileDriver(new JsonLoader());
    }

    /**
     * Get YAML files driver.
     *
     * @return DefinitionFileDriver
     */
    public static function getYamlDriver(): DefinitionFileDriver
    {
        return new DefinitionFileDriver(new YamlLoader());
    }
}
