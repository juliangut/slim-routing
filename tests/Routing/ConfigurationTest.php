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

use Jgut\Mapping\Metadata\MetadataResolver;
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use Jgut\Slim\Routing\Naming\CamelCase;
use Jgut\Slim\Routing\Naming\SnakeCase;
use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandler;
use Jgut\Slim\Routing\Route\Resolver;
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
            'any' => '[^}]+',
            'numeric' => '[0-9]+',
            'number' => '[0-9]+',
            'alpha' => '[a-zA-Z]+',
            'word' => '[a-zA-Z]+',
            'alnum' => '[a-zA-Z0-9]+',
            'slug' => '[a-zA-Z0-9-]+',
            'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
            'mongoid' => '[0-9a-f]{24}',
        ];

        $configuration = new Configuration();

        self::assertEmpty($configuration->getSources());
        self::assertEquals($defaultAliasList, $configuration->getPlaceholderAliases());
        self::assertInstanceOf(MetadataResolver::class, $configuration->getMetadataResolver());
        self::assertInstanceOf(Resolver::class, $configuration->getRouteResolver());
        self::assertInstanceOf(SnakeCase::class, $configuration->getNamingStrategy());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following configuration parameters are not recognized: unknown
     */
    public function testUnknownParameter()
    {
        new Configuration(['unknown' => 'unknown']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Mapping source must be a string, array or .+\DriverInterface, integer given/
     */
    public function testBadSource()
    {
        new Configuration(['sources' => [10]]);
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
            'any' => '[^}]+',
            'numeric' => '[0-9]+',
            'number' => '[0-9]+',
            'alpha' => '[a-zA-Z]+',
            'word' => '[a-zA-Z]+',
            'alnum' => '[a-zA-Z0-9]+',
            'slug' => '[a-zA-Z0-9-]+',
            'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
            'mongoid' => '[0-9a-f]{24}',
            'dni' => '\d+[A-Z]',
        ];

        $configuration = new Configuration([
            'placeholderAliases' => [
                'dni' => '\d+[A-Z]',
            ],
        ]);

        self::assertEquals($aliasList, $configuration->getPlaceholderAliases());
    }

    public function testMetadataResolver()
    {
        $metadataResolver = new MetadataResolver(new DriverFactory());

        $configuration = new Configuration(['metadataResolver' => $metadataResolver]);

        self::assertEquals($metadataResolver, $configuration->getMetadataResolver());
    }

    public function testRouteResolver()
    {
        $routeResolver = new Resolver(new Configuration());

        $configuration = new Configuration(['routeResolver' => $routeResolver]);

        self::assertEquals($routeResolver, $configuration->getRouteResolver());
    }

    public function testNamingStrategy()
    {
        $configuration = new Configuration(['namingStrategy' => new CamelCase()]);

        self::assertInstanceOf(CamelCase::class, $configuration->getNamingStrategy());
    }

    public function testResponseHandlers()
    {
        $handler = $this->getMockBuilder(ResponseTypeHandler::class)
            ->getMock();
        /* @var ResponseTypeHandler $handler */
        $handlers = [
            'responseTypeClass' => $handler,
        ];

        $configuration = new Configuration([
            'responseHandlers' => $handlers,
        ]);

        self::assertEquals($handlers, $configuration->getResponseHandlers());
    }
}
