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

namespace Jgut\Slim\Routing\Mapping\Source;

use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use Jgut\Slim\Routing\Mapping\Driver\DriverInterface;

/**
 * Annotations mapping source.
 */
class AnnotationSource extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function getDriver(): DriverInterface
    {
        if (!$this->driver) {
            $this->driver = DriverFactory::getAnnotationDriver();
        }

        return $this->driver;
    }
}