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

namespace Jgut\Slim\Routing\Tests\Mapping\Metadata;

use Jgut\Slim\Routing\Tests\Stubs\AbstractMetadataStub;
use PHPUnit\Framework\TestCase;

/**
 * Abstract metadata tests.
 */
class AbstractMetadataTest extends TestCase
{
    /**
     * @var \Jgut\Slim\Routing\Mapping\Metadata\AbstractMetadata
     */
    protected $metadata;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->metadata = new AbstractMetadataStub();
    }

    public function testDefaults()
    {
        self::assertNull($this->metadata->getPattern());
        self::assertEquals([], $this->metadata->getPlaceholders());
        self::assertEquals([], $this->metadata->getParameters());
        self::assertEquals([], $this->metadata->getMiddleware());
    }

    public function testEmptyPattern()
    {
        $this->expectExceptionMessage('Pattern can not be empty');
        $this->expectException(\Jgut\Mapping\Exception\MetadataException::class);
        $this->metadata->setPattern('');
    }

    public function testInvalidPattern()
    {
        $this->expectExceptionMessage('Placeholder matching "[0-9]+" must be defined on placeholders parameter');
        $this->expectException(\Jgut\Mapping\Exception\MetadataException::class);
        $this->metadata->setPattern('{path}/to/{id:[0-9]+}');
    }

    public function testPattern()
    {
        $path = 'home/route/path/{id}';

        $this->metadata->setPattern('/' . $path);

        self::assertEquals($path, $this->metadata->getPattern());
    }

    public function testPlaceholders()
    {
        $placeholders = ['id' => '[0-9]{5}'];

        $this->metadata->setPlaceholders($placeholders);

        self::assertEquals($placeholders, $this->metadata->getPlaceholders());
    }

    public function testParameters()
    {
        $parameters = ['id' => 'int'];

        $this->metadata->setParameters($parameters);

        self::assertEquals($parameters, $this->metadata->getParameters());
    }

    public function testMiddleware()
    {
        $middleware = ['middlewareOne', 'middlewareTwo'];

        $this->metadata->setMiddleware($middleware);

        self::assertEquals($middleware, $this->metadata->getMiddleware());
    }
}
