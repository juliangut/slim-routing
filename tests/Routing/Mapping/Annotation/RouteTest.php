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

namespace Jgut\Slim\Routing\Tests\Mapping\Annotation;

use Jgut\Slim\Routing\Mapping\Annotation\Route;
use PHPUnit\Framework\TestCase;

/**
 * Route annotation tests.
 */
class RouteTest extends TestCase
{
    /**
     * @var Route
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->annotation = new Route([]);
    }

    public function testDefaults(): void
    {
        static::assertEquals('', $this->annotation->getName());
        static::assertNull($this->annotation->getTransformer());
        static::assertEquals(['GET'], $this->annotation->getMethods());
        static::assertEquals(0, $this->annotation->getPriority());
        static::assertFalse($this->annotation->isXmlHttpRequest());
    }

    public function testWrongName(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\AnnotationException::class);
        $this->expectExceptionMessage('Route name must not contain spaces');

        $this->annotation->setName('a name');
    }

    public function testEmptyName(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\AnnotationException::class);
        $this->expectExceptionMessage('Route name can not be empty');

        $this->annotation->setName('');
    }

    public function testName(): void
    {
        $this->annotation->setName('routeName');

        static::assertEquals('routeName', $this->annotation->getName());
    }

    public function testTransformer(): void
    {
        $this->annotation->setTransformer('transformer');

        static::assertEquals('transformer', $this->annotation->getTransformer());
    }

    public function testInvalidMethodsType(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\AnnotationException::class);
        $this->expectExceptionMessage('Route annotation methods must be strings. "integer" given');

        $this->annotation->setMethods([10]);
    }

    public function testEmptyMethods(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\AnnotationException::class);
        $this->expectExceptionMessage('Route annotation methods can not be empty');

        $this->annotation->setMethods('');
    }

    public function testWrongMethodCount(): void
    {
        $this->expectException(\Jgut\Mapping\Exception\AnnotationException::class);
        $this->expectExceptionMessage('Route "ANY" method cannot be defined with other methods');

        $this->annotation->setMethods(['GET', 'ANY']);
    }

    public function testMethods(): void
    {
        $methods = ['GET', 'POST'];

        $this->annotation->setMethods($methods);

        static::assertEquals($methods, $this->annotation->getMethods());
    }

    public function testPriority(): void
    {
        $this->annotation->setPriority(-10);

        static::assertEquals(-10, $this->annotation->getPriority());
    }

    public function testXmlHttpRequest(): void
    {
        $this->annotation->setXmlHttpRequest(true);

        static::assertTrue($this->annotation->isXmlHttpRequest());
    }
}
