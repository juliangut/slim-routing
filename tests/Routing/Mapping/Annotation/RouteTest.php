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
        self::assertEquals('', $this->annotation->getName());
        self::assertNull($this->annotation->getTransformer());
        self::assertEquals(['GET'], $this->annotation->getMethods());
        self::assertEquals(0, $this->annotation->getPriority());
        self::assertFalse($this->annotation->isXmlHttpRequest());
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

        self::assertEquals('routeName', $this->annotation->getName());
    }

    public function testTransformer(): void
    {
        $this->annotation->setTransformer('transformer');

        self::assertEquals('transformer', $this->annotation->getTransformer());
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

        self::assertEquals($methods, $this->annotation->getMethods());
    }

    public function testPriority(): void
    {
        $this->annotation->setPriority(-10);

        self::assertEquals(-10, $this->annotation->getPriority());
    }

    public function testXmlHttpRequest(): void
    {
        $this->annotation->setXmlHttpRequest(true);

        self::assertTrue($this->annotation->isXmlHttpRequest());
    }
}
