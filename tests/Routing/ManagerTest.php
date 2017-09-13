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

namespace Jgut\Slim\Routing\Tests;

use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Loader\LoaderInterface;
use Jgut\Slim\Routing\Route;
use Jgut\Slim\Routing\RouteCompiler;
use Jgut\Slim\Routing\Tests\Stubs\ManagerStub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Router;

/**
 * Routing manager tests.
 */
class ManagerTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There are duplicated route names: name
     */
    public function testDuplicatedRouteNames()
    {
        $routes = [
            (new Route())->setMethods(['GET'])->setName('name'),
            (new Route())->setMethods(['POST'])->setName('name'),
        ];

        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        /* @var LoaderInterface $loader */

        $compiler = $this->getMockBuilder(RouteCompiler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $compiler->expects(self::once())
            ->method('getRoutes')
            ->will(self::returnValue($routes));
        /* @var RouteCompiler $compiler */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        $configuration->expects(self::once())
            ->method('getSources')
            ->will(self::returnValue([__DIR__]));
        /* @var Configuration $configuration */

        $manager = new ManagerStub($configuration, $loader);
        $manager->setCompiler($compiler);

        $manager->getRoutes();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage There are duplicated routes: POST /path/{[0-9]+}
     */
    public function testDuplicatedRoutePaths()
    {
        $routes = [
            (new Route())->setMethods(['GET', 'POST'])->setPattern('/path/{id}')->setPlaceholders(['id' => '[0-9]+']),
            (new Route())->setMethods(['POST'])->setPattern('/path/{id}')->setPlaceholders(['id' => '[0-9]+']),
        ];

        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        /* @var LoaderInterface $loader */

        $compiler = $this->getMockBuilder(RouteCompiler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $compiler->expects(self::once())
            ->method('getRoutes')
            ->will(self::returnValue($routes));
        /* @var RouteCompiler $compiler */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        $configuration->expects(self::once())
            ->method('getSources')
            ->will(self::returnValue([__DIR__]));
        /* @var Configuration $configuration */

        $manager = new ManagerStub($configuration, $loader);
        $manager->setCompiler($compiler);

        $manager->getRoutes();
    }

    public function testGetRoutes()
    {
        $routes = [
            (new Route())->setMethods(['POST'])->setPattern('/path/{id}'),
            (new Route())->setMethods(['GET'])->setPattern('/path/{id}')->setPriority(-10),
        ];

        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        /* @var LoaderInterface $loader */

        $compiler = $this->getMockBuilder(RouteCompiler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $compiler->expects(self::once())
            ->method('getRoutes')
            ->will(self::returnValue($routes));
        /* @var RouteCompiler $compiler */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        $configuration->expects(self::once())
            ->method('getSources')
            ->will(self::returnValue([__DIR__]));
        /* @var Configuration $configuration */

        $manager = new ManagerStub($configuration, $loader);
        $manager->setCompiler($compiler);

        $loaded = $manager->getRoutes();

        self::assertEquals(array_reverse($routes), $loaded);
    }

    public function testCompiledRoutes()
    {
        $routes = [
            (new Route())->setMethods(['POST'])->setPattern('/path/{id}')->setName('one'),
            (new Route())->setMethods(['GET'])->setPattern('/path/{id}'),
        ];
        $router = new Router();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects(self::exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($router, ['outputBuffering' => 'append']);
        /* @var ContainerInterface $container */

        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMock();
        /* @var LoaderInterface $loader */

        $compiler = $this->getMockBuilder(RouteCompiler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $compiler->expects(self::once())
            ->method('getRoutes')
            ->will(self::returnValue($routes));
        /* @var RouteCompiler $compiler */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        $configuration->expects(self::once())
            ->method('getSources')
            ->will(self::returnValue([__DIR__]));
        /* @var Configuration $configuration */

        $manager = new ManagerStub($configuration, $loader);
        $manager->setCompiler($compiler);

        $manager->registerRoutes($container);

        /* @var \Slim\Route[] $loaded */
        $loaded = $router->getRoutes();

        self::assertCount(2, $loaded);
        self::assertEquals('one', array_shift($loaded)->getName());
    }
}
