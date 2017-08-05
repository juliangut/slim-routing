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

namespace Jgut\Slim\Routing\Tests\Annotation;

use Jgut\Slim\Routing\Annotation\Router;
use PHPUnit\Framework\TestCase;

/**
 * Router annotation tests.
 */
class RouterTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The following annotation parameters are not recognized: whatever
     */
    public function testNoParameters()
    {
        new Router(['whatever' => 'invalid']);
    }
}
