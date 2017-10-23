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
use Jgut\Mapping\Driver\AbstractDriverFactory;
use Jgut\Mapping\Driver\DriverInterface;

/**
 * Custom mapping driver factory.
 */
class DriverFactory extends AbstractDriverFactory
{
    /**
     * {@inheritdoc}
     */
    protected static function getAnnotationDriver(array $paths): DriverInterface
    {
        return new AnnotationDriver($paths, new AnnotationReader());
    }

    /**
     * {@inheritdoc}
     */
    protected static function getPhpDriver(array $paths): DriverInterface
    {
        return new PhpDriver($paths);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getXmlDriver(array $paths): DriverInterface
    {
        return new XmlDriver($paths);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getJsonDriver(array $paths): DriverInterface
    {
        return new JsonDriver($paths);
    }

    /**
     * {@inheritdoc}
     */
    protected static function getYamlDriver(array $paths): DriverInterface
    {
        return new YamlDriver($paths);
    }
}
