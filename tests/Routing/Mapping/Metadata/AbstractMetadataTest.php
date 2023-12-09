<?php

/*
 * (c) 2017-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Mapping\Metadata;

use Jgut\Mapping\Exception\MetadataException;
use Jgut\Slim\Routing\Tests\Stubs\AbstractMetadataStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AbstractMetadataTest extends TestCase
{
    public function testDefaults(): void
    {
        $metadata = new AbstractMetadataStub();

        static::assertNull($metadata->getPattern());
        static::assertEquals([], $metadata->getPlaceholders());
        static::assertEquals([], $metadata->getParameters());
        static::assertEquals([], $metadata->getTransformers());
        static::assertEquals([], $metadata->getArguments());
        static::assertEquals([], $metadata->getMiddlewares());
    }

    public function testEmptyPattern(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Pattern can not be empty');

        (new AbstractMetadataStub())->setPattern('');
    }

    public function testInvalidPattern(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Placeholder matching "[0-9]+" must be defined on placeholders parameter');

        (new AbstractMetadataStub())->setPattern('{path}/to/{id:[0-9]+}');
    }

    public function testPattern(): void
    {
        $path = 'home/route/path/{id}';

        $metadata = new AbstractMetadataStub();

        $metadata->setPattern('/' . $path . '/');

        static::assertEquals($path, $metadata->getPattern());
    }

    public function testPlaceholders(): void
    {
        $placeholders = ['id' => '[0-9]{5}'];

        $metadata = new AbstractMetadataStub();

        $metadata->setPlaceholders($placeholders);

        static::assertEquals($placeholders, $metadata->getPlaceholders());
    }

    public function testParameters(): void
    {
        $parameters = ['id' => 'int'];

        $metadata = new AbstractMetadataStub();

        $metadata->setParameters($parameters);

        static::assertEquals($parameters, $metadata->getParameters());
    }

    public function testTransformers(): void
    {
        $transformers = ['transformerOne', 'transformerTwo'];

        $metadata = new AbstractMetadataStub();

        $metadata->setTransformers($transformers);

        static::assertEquals(['transformerOne', 'transformerTwo'], $metadata->getTransformers());
    }

    public function testArguments(): void
    {
        $arguments = ['scope' => 'public'];

        $metadata = new AbstractMetadataStub();

        $metadata->setArguments($arguments);

        static::assertEquals($arguments, $metadata->getArguments());
    }

    public function testMiddleware(): void
    {
        $middleware = ['middlewareOne', 'middlewareTwo'];

        $metadata = new AbstractMetadataStub();

        $metadata->setMiddlewares($middleware);

        static::assertEquals(['middlewareOne', 'middlewareTwo'], $metadata->getMiddlewares());
    }
}
