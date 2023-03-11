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

use Jgut\Mapping\Exception\AnnotationException;
use Jgut\Slim\Routing\Mapping\Annotation\Route;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class RouteTest extends TestCase
{
    public function testWrongName(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Route name must not contain spaces');

        (new Route([]))->setName('a name');
    }

    public function testEmptyName(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Route name can not be empty');

        (new Route([]))->setName('');
    }

    public function testInvalidMethodsType(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Route annotation methods must be strings. "integer" given.');

        (new Route([]))->setMethods([10]);
    }

    public function testEmptyMethods(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Route annotation methods can not be empty');

        (new Route([]))->setMethods('');
    }

    public function testWrongMethodCount(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Route "ANY" method cannot be defined with other methods');

        (new Route([]))->setMethods(['GET', 'ANY']);
    }
}
