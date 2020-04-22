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

use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Group metadata tests.
 */
class GroupMetadataTest extends TestCase
{
    /**
     * @var GroupMetadata
     */
    protected $group;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->group = new GroupMetadata();
    }

    public function testDefaults(): void
    {
        static::assertNull($this->group->getParent());
        static::assertNull($this->group->getPrefix());
    }

    public function testParent(): void
    {
        $group = $this->getMockBuilder(GroupMetadata::class)
            ->getMock();

        $this->group->setParent($group);

        static::assertEquals($group, $this->group->getParent());
    }

    public function testPrefix(): void
    {
        $this->group->setPrefix('prefix');

        static::assertEquals('prefix', $this->group->getPrefix());
    }
}
