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

namespace Jgut\Slim\Routing\Tests;

use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Naming\CamelCase;
use Jgut\Slim\Routing\Naming\SnakeCase;
use PHPUnit\Framework\TestCase;

/**
 * Configuration tests.
 */
class ConfigurationTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Configurations must be an iterable
     */
    public function testInvalidConfigurations()
    {
        new Configuration('');
    }

    public function testDefaults()
    {
        $defaultAliasList = [
            'numeric' => '\d+',
            'alpha' => '[a-zA-Z]+',
            'alnum' => '[a-zA-Z0-9]+',
            'any' => '.+',
        ];

        $configuration = new Configuration();

        self::assertEmpty($configuration->getSources());
        self::assertEquals($defaultAliasList, $configuration->getPlaceholderAliases());
        self::assertInstanceOf(SnakeCase::class, $configuration->getNamingStrategy());
    }

    public function testSourcePaths()
    {
        $paths = [
            '/path/to/directory',
            '/path/to/file.php',
        ];

        $configuration = new Configuration(['sources' => $paths]);

        self::assertEquals($paths, $configuration->getSources());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Placeholder pattern "notRegex~" is not a valid regex
     */
    public function testBadPlaceholderAlias()
    {
        new Configuration(['placeholderAliases' => ['tlf' => 'notRegex~']]);
    }

    public function testPlaceholderAliases()
    {
        $aliasList = [
            'numeric' => '\d+',
            'alpha' => '[a-zA-Z]+',
            'alnum' => '[a-zA-Z0-9]+',
            'any' => '.+',
            'dni' => '\d+[A-Z]',
        ];

        $configuration = new Configuration([
            'placeholderAliases' => [
                'dni' => '\d+[A-Z]',
            ],
        ]);

        self::assertEquals($aliasList, $configuration->getPlaceholderAliases());
    }

    public function testNamingStrategy()
    {
        $configuration = new Configuration(['namingStrategy' => new CamelCase()]);

        self::assertInstanceOf(CamelCase::class, $configuration->getNamingStrategy());
    }
}
