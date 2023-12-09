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
use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class GroupMetadataTest extends TestCase
{
    public function testDefaults(): void
    {
        $group = new GroupMetadata();

        static::assertNull($group->getParent());
        static::assertNull($group->getPrefix());
    }

    public function testInvalidPrefix(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Group prefix must not contain spaces.');

        (new GroupMetadata())->setPrefix('invalid value');
    }

    public function testEmptyPrefix(): void
    {
        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Group prefix can not be an empty string.');

        (new GroupMetadata())->setPrefix('');
    }
}
