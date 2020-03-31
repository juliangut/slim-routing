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
use Jgut\Slim\Routing\Route\RouteResolver;
use PHPUnit\Framework\TestCase;

/**
 * Configuration tests.
 */
class ConfigurationTest extends TestCase
{
    public function testInvalidConfigurations(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configurations must be an iterable');

        new Configuration('');
    }

    public function testDefaults(): void
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

        static::assertEmpty($configuration->getSources());
        static::assertFalse($configuration->hasTrailingSlash());
        static::assertEquals($defaultAliasList, $configuration->getPlaceholderAliases());
        static::assertInstanceOf(MetadataResolver::class, $configuration->getMetadataResolver());
        static::assertInstanceOf(RouteResolver::class, $configuration->getRouteResolver());
        static::assertInstanceOf(SnakeCase::class, $configuration->getNamingStrategy());
    }

    public function testUnknownParameter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The following configuration parameters are not recognized: unknown');

        new Configuration(new \ArrayIterator(['unknown' => 'unknown']));
    }

    public function testBadSource(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp(
            '/Mapping source must be a string, array or .+\DriverInterface, integer given/'
        );

        new Configuration(['sources' => [10]]);
    }

    public function testSourcePaths(): void
    {
        $paths = [
            '/path/to/directory',
            '/path/to/file.php',
        ];

        $configuration = new Configuration(['sources' => $paths]);

        static::assertEquals($paths, $configuration->getSources());
    }

    public function testTrailingSlash(): void
    {
        $configuration = new Configuration(['trailingSlash' => true]);

        static::assertTrue($configuration->hasTrailingSlash());
    }

    public function testBadPlaceholderAlias(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Placeholder pattern "notRegex~" is not a valid regex');

        new Configuration(['placeholderAliases' => ['tlf' => 'notRegex~']]);
    }

    public function testPlaceholderAliases(): void
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

        static::assertEquals($aliasList, $configuration->getPlaceholderAliases());
    }

    public function testMetadataResolver(): void
    {
        $metadataResolver = new MetadataResolver(new DriverFactory());

        $configuration = new Configuration(['metadataResolver' => $metadataResolver]);

        static::assertEquals($metadataResolver, $configuration->getMetadataResolver());
    }

    public function testRouteResolver(): void
    {
        $routeResolver = new RouteResolver(new Configuration());

        $configuration = new Configuration(['routeResolver' => $routeResolver]);

        static::assertEquals($routeResolver, $configuration->getRouteResolver());
    }

    public function testNamingStrategy(): void
    {
        $configuration = new Configuration(['namingStrategy' => new CamelCase()]);

        static::assertInstanceOf(CamelCase::class, $configuration->getNamingStrategy());
    }
}
