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

class DriverFactory extends AbstractDriverFactory
{
    protected function getPhpDriver(array $paths): DriverInterface
    {
        return new PhpDriver($paths);
    }

    protected function getXmlDriver(array $paths): DriverInterface
    {
        return new XmlDriver($paths);
    }

    protected function getJsonDriver(array $paths): DriverInterface
    {
        return new JsonDriver($paths);
    }

    protected function getYamlDriver(array $paths): DriverInterface
    {
        return new YamlDriver($paths);
    }

    protected function getAttributeDriver(array $paths): DriverInterface
    {
        return new AttributeDriver($paths);
    }

    protected function getAnnotationDriver(array $paths): DriverInterface
    {
        return new AnnotationDriver($paths, new AnnotationReader());
    }
}
