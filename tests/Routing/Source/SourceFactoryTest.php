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

use Jgut\Slim\Routing\Source\AnnotationSource;
use Jgut\Slim\Routing\Source\PhpSource;
use Jgut\Slim\Routing\Source\SourceFactory;
use Jgut\Slim\Routing\Source\YamlSource;
use PHPUnit\Framework\TestCase;

/**
 * Source factory tests.
 */
class SourceFactoryTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^".+" routing source. Must be a SourceInterface, a directory or a path$/
     */
    public function testInvalidSource()
    {
        SourceFactory::getSource(10);
    }

    public function testSourceFromInterface()
    {
        $source = new AnnotationSource('');

        self::assertEquals($source, SourceFactory::getSource($source));
    }

    public function testSourceFromDirectory()
    {
        $source = SourceFactory::getSource(__DIR__);

        self::assertInstanceOf(AnnotationSource::class, $source);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown "md" extension
     */
    public function testInvalidFileType()
    {
        SourceFactory::getSource(__DIR__ . '/../../../README.md');
    }

    public function testSourceFromPhpFile()
    {
        $source = SourceFactory::getSource(__DIR__ . '/../Files/files/valid/routingA.php');

        self::assertInstanceOf(PhpSource::class, $source);
    }

    public function testSourceFromYamlFile()
    {
        $source = SourceFactory::getSource(__DIR__ . '/../Files/files/valid/routingA.yml');

        self::assertInstanceOf(YamlSource::class, $source);

        $source = SourceFactory::getSource(__DIR__ . '/../Files/files/valid/routingB.yaml');

        self::assertInstanceOf(YamlSource::class, $source);
    }
}
