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
use Jgut\Slim\Routing\Manager;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Resolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Route;
use Slim\Router;

/**
 * Routing manager tests.
 */
class ManagerTest extends TestCase
{
    public function testDefaultResolver()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        /* @var Configuration $configuration */

        $manager = new Manager($configuration);

        self::assertInstanceOf(Resolver::class, $manager->getResolver());
    }

    public function testResolver()
    {
        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        /* @var Configuration $configuration */

        $resolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Resolver $resolver */

        $manager = new Manager($configuration);
        $manager->setResolver($resolver);

        self::assertEquals($resolver, $manager->getResolver());
    }

    public function testRoutes()
    {
        $routesMetadata = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setInvokable(['one', 'action']),
            (new RouteMetadata())
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setName('two')
                ->setMiddleware(['twoMiddleware'])
                ->setInvokable(['two', 'action']),
        ];

        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getSources'])
            ->getMock();
        $configuration->expects(self::once())
            ->method('getSources')
            ->will(self::returnValue([__DIR__ . '/Files/Annotation/Valid']));
        /* @var Configuration $configuration */

        $resolver = $this->getMockBuilder(Resolver::class)
            ->setConstructorArgs([$configuration])
            ->setMethods(['sort'])
            ->getMock();
        $resolver->expects(self::once())
            ->method('sort')
            ->will(self::returnValue($routesMetadata));
        /* @var Resolver $resolver */

        $manager = new Manager($configuration);
        $manager->setResolver($resolver);

        $routes = $manager->getRoutes();

        self::assertCount(2, $routes);
    }

    public function testNoRoutes()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->never())
            ->method('get');
        /* @var ContainerInterface $container */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        /* @var Configuration $configuration */

        $manager = new Manager($configuration);

        $manager->registerRoutes($container);
    }

    public function testRouteRegistration()
    {
        $routes = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setInvokable(['one', 'action']),
            (new RouteMetadata())
                ->setMethods(['POST'])
                ->setPattern('two')
                ->setName('two')
                ->setMiddleware(['twoMiddleware'])
                ->setInvokable(['two', 'action']),
        ];

        $router = $this->getMockBuilder(Router::class)
            ->getMock();
        $router->expects(self::exactly(2))
            ->method('map')
            ->will($this->returnValue(new Route('', '', '')));

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects(self::any())
            ->method('get')
            ->willReturnOnConsecutiveCalls($router, ['outputBuffering' => 'append']);
        /* @var ContainerInterface $container */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getSources'])
            ->getMock();
        $configuration->expects(self::once())
            ->method('getSources')
            ->will(self::returnValue([__DIR__ . '/Files/Annotation/Valid']));
        /* @var Configuration $configuration */

        $resolver = $this->getMockBuilder(Resolver::class)
            ->setConstructorArgs([$configuration])
            ->setMethods(['sort'])
            ->getMock();
        $resolver->expects(self::once())
            ->method('sort')
            ->will(self::returnValue($routes));
        /* @var Resolver $resolver */

        $manager = new Manager($configuration);
        $manager->setResolver($resolver);

        $manager->registerRoutes($container);
    }
}
