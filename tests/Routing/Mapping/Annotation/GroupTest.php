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
use Jgut\Slim\Routing\Mapping\Annotation\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class GroupTest extends TestCase
{
    public function testDefaults(): void
    {
        $annotation = new Group([]);

        static::assertNull($annotation->getParent());
    }

    public function testWrongPrefix(): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Group prefixes must not contain spaces');

        new Group(['prefix' => 'a prefix']);
    }
}
