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

namespace Jgut\Slim\Routing\Tests\Mapping\Driver;

use Jgut\Mapping\Exception\DriverException;
use Jgut\Slim\Routing\Mapping\Driver\FileMappingTrait;
use Jgut\Slim\Routing\Mapping\Driver\JsonDriver;
use Jgut\Slim\Routing\Mapping\Driver\PhpDriver;
use Jgut\Slim\Routing\Mapping\Driver\XmlDriver;
use Jgut\Slim\Routing\Mapping\Driver\YamlDriver;
use stdClass;

/**
 * @internal
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class FileMappingTraitTest extends AbstractDriverTest
{
    public function testNoMapping(): void
    {
        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn(['']);

        static::assertEquals([], $driver->getMetadata());
    }

    public function testMissingInvokable(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Route invokable definition missing');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidInvokable(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Route invokable does not seem to be supported by Slim router');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                ['invokable' => 10],
            ]);

        $driver->getMetadata();
    }

    public function testEmptyMethods(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Route methods can not be empty');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'methods' => '',
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidMethods(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Route methods must be a string or string array. "integer" given.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'methods' => 10,
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidPlaceholdersType(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Placeholders must be an array.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'placeholders' => 'invalid',
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidPlaceholdersKeys(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Placeholder keys must be all strings.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'placeholders' => ['invalid'],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidPlaceholdersElementType(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Placeholders must be strings. "integer" given.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'placeholders' => ['key' => 100],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidMiddleware(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Middleware must be a string or string array. "integer" given.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'middleware' => 10,
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidParameters(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Parameters keys must be all strings');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'parameters' => ['invalid'],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidArguments(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Arguments keys must be all strings');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'arguments' => ['invalid'],
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidTransformer(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Route transformer must be a string. "object" given.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'transformer' => new stdClass(),
                ],
            ]);

        $driver->getMetadata();
    }

    public function testInvalidXmlHttpRequest(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('XMLHTTPRequest must be a boolean. "string" given.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'xmlHttpRequest' => 'true',
                ],
            ]);

        $driver->getMetadata();
    }

    public function testPriority(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('Route priority must be an integer. "string" given.');

        $driver = $this->getMockForTrait(FileMappingTrait::class);
        $driver->expects(static::once())
            ->method('getMappingData')
            ->willReturn([
                [
                    'invokable' => 'callable',
                    'priority' => '10',
                ],
            ]);

        $driver->getMetadata();
    }

    public function testPhpResources(): void
    {
        $driver = new PhpDriver([
            __DIR__ . '/../Files/Files/Valid/Php/DependentRoute.php',
            __DIR__ . '/../Files/Files/Valid/Php/GroupedRoute.php',
            __DIR__ . '/../Files/Files/Valid/Php/SingleRoute.php',
        ]);

        $this->checkResources($driver, 'Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute');
    }

    public function testJsonResources(): void
    {
        $driver = new JsonDriver([
            __DIR__ . '/../Files/Files/Valid/Json/DependentRoute.json',
            __DIR__ . '/../Files/Files/Valid/Json/GroupedRoute.json',
            __DIR__ . '/../Files/Files/Valid/Json/SingleRoute.json',
        ]);

        $this->checkResources($driver, 'Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute');
    }

    public function testXmlResources(): void
    {
        $driver = new XmlDriver([
            __DIR__ . '/../Files/Files/Valid/Xml/DependentRoute.xml',
            __DIR__ . '/../Files/Files/Valid/Xml/GroupedRoute.xml',
            __DIR__ . '/../Files/Files/Valid/Xml/SingleRoute.xml',
        ]);

        $this->checkResources($driver, 'Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute');
    }

    public function testYamlResources(): void
    {
        $driver = new YamlDriver([
            __DIR__ . '/../Files/Files/Valid/Yaml/DependentRoute.yaml',
            __DIR__ . '/../Files/Files/Valid/Yaml/GroupedRoute.yaml',
            __DIR__ . '/../Files/Files/Valid/Yaml/SingleRoute.yaml',
        ]);

        $this->checkResources($driver, 'Jgut\Slim\Routing\Tests\Mapping\Files\Classes\Valid\Attribute');
    }
}
