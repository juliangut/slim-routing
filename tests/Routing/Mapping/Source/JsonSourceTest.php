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

namespace Jgut\Slim\Routing\Tests\Source;

use Jgut\Slim\Routing\Mapping\Driver\DefinitionFileDriver;
use Jgut\Slim\Routing\Mapping\Source\JsonSource;
use PHPUnit\Framework\TestCase;

/**
 * JSON files mapping source tests.
 */
class JsonSourceTest extends TestCase
{
    public function testDriver()
    {
        $source = new JsonSource([]);

        self::assertInstanceOf(DefinitionFileDriver::class, $source->getDriver());
    }
}
