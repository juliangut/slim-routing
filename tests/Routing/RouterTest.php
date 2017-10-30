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
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Mapping\Resolver;
use Jgut\Slim\Routing\Router;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Route loader router tests.
 */
class RouterTest extends TestCase
{
    public function testDefaultResolver()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        /* @var ContainerInterface $container */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        /* @var Configuration $configuration */

        $router = new Router($configuration);
        $router->setContainer($container);

        $this->assertInstanceOf(Resolver::class, $router->getResolver());
    }

    public function testResolver()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        /* @var ContainerInterface $container */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->getMock();
        /* @var Configuration $configuration */

        $resolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        /* @var Resolver $resolver */

        $router = new Router($configuration);
        $router->setContainer($container);
        $router->setResolver($resolver);

        $this->assertEquals($resolver, $router->getResolver());
    }

    public function testRoutes()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValue(['outputBuffering' => 'append']));
        /* @var ContainerInterface $container */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getSources'])
            ->getMock();
        $configuration->expects($this->once())
            ->method('getSources')
            ->will($this->returnValue([__DIR__ . '/Files/Annotation/Valid']));
        /* @var Configuration $configuration */

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

        $resolver = $this->getMockBuilder(Resolver::class)
            ->setConstructorArgs([$configuration])
            ->setMethods(['sort'])
            ->getMock();
        $resolver->expects($this->once())
            ->method('sort')
            ->will($this->returnValue($routesMetadata));
        /* @var Resolver $resolver */

        $router = new Router($configuration);
        $router->setContainer($container);
        $router->setResolver($resolver);

        $routes = $router->getRoutes();

        $this->assertCount(2, $routes);
    }
}
