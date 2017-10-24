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

namespace Jgut\Slim\Routing\Tests\Router;

use Jgut\Slim\Routing\Router\Router;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Router tests.
 */
class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->router = new Router();
    }

    public function testContainer()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        /* @var ContainerInterface $container */

        $this->router->setContainer($container);

        self::assertEquals($container, $this->router->getContainer());
    }
}
