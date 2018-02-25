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

use FastRoute\Dispatcher;
use Jgut\Slim\Routing\Configuration;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Resolver;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Router;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Http\Environment;
use Slim\Http\Request;

/**
 * Route loader router tests.
 */
class RouterTest extends TestCase
{
    public function testRoutes()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValue(['outputBuffering' => 'append']));
        /* @var ContainerInterface $container */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getSources', 'getRouteResolver'])
            ->getMock();
        $configuration->expects($this->once())
            ->method('getSources')
            ->will($this->returnValue([__DIR__ . '/Files/Annotation/Valid']));

        $routesMetadata = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('one/{id}')
                ->setPlaceholders(['id' => 'numeric'])
                ->setInvokable(['one', 'action'])
                ->setXmlHttpRequest(true),
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

        $configuration->expects($this->any())
            ->method('getRouteResolver')
            ->will($this->returnValue($resolver));
        /* @var Configuration $configuration */

        $router = new Router($configuration);
        $router->setContainer($container);

        $this->assertCount(2, $router->getRoutes());
    }

    public function testDispatcher()
    {
        $dispatchData = [
            [
                'GET' => ['/one' => 'route0'],
                'POST' => [],
            ],
            [
                'GET' => [],
                'POST' => [],
            ],
        ];

        $routesFile = vfsStream::newFile('routes.cache');

        $fileRoot = vfsStream::setup('rootDir');
        $fileRoot->addChild($routesFile);

        \file_put_contents($routesFile->url(), '<?php return ' . \var_export($dispatchData, true) . ';');

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValue(['outputBuffering' => 'append']));
        /* @var ContainerInterface $container */

        $configuration = $this->getMockBuilder(Configuration::class)
            ->setMethods(['getSources', 'getRouteResolver'])
            ->getMock();
        $configuration->expects($this->once())
            ->method('getSources')
            ->will($this->returnValue([__DIR__ . '/Files/Annotation/Valid']));
        /* @var Configuration $configuration */

        $routesMetadata = [
            (new RouteMetadata())
                ->setMethods(['GET'])
                ->setPattern('one')
                ->setInvokable(['one', 'action']),
        ];

        $resolver = $this->getMockBuilder(Resolver::class)
            ->setConstructorArgs([$configuration])
            ->setMethods(['sort'])
            ->getMock();
        $resolver->expects($this->once())
            ->method('sort')
            ->will($this->returnValue($routesMetadata));
        /* @var Resolver $resolver */

        $configuration->expects($this->any())
            ->method('getRouteResolver')
            ->will($this->returnValue($resolver));
        /* @var Configuration $configuration */

        $router = new Router($configuration);
        $router->setContainer($container);
        $router->setCacheFile($routesFile->url());

        $request = Request::createFromEnvironment(Environment::mock(['REQUEST_URI' => 'fake.com/one']));

        $routeInfo = $router->dispatch($request);

        $this->assertCount(3, $routeInfo);
        $this->assertEquals(Dispatcher::FOUND, $routeInfo[0]);

        $this->assertInstanceOf(Route::class, $router->lookupRoute($routeInfo[1]));
    }
}
