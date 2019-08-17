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

namespace Jgut\Slim\Routing\Tests\Route;

use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Tests\Stubs\AbstractTransformerStub;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\CallableResolverInterface;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Response type aware route tests.
 */
class RouteTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->request = (new ServerRequestFactory())->createServerRequest('GET', '/');
    }

    public function testNonXmlHttpRequestRequest(): void
    {
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        /** @var CallableResolverInterface $callableResolver */
        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(true));
        /* @var RouteMetadata $metadata */

        $route = new Route(
            ['GET'],
            '/',
            function (): void {
            },
            new ResponseFactory(),
            $callableResolver,
            $metadata
        );

        $response = $route->run($this->request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^Parameter transformer should implement .+\\ParameterTransformer, ".+" given$/
     */
    public function testWrongParameterTransformer(): void
    {
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        /** @var CallableResolverInterface $callableResolver */
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValue(new \stdClass()));
        /* @var ContainerInterface $container */

        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getTransformer')
            ->will($this->returnValue('transformer'));
        /* @var RouteMetadata $metadata */

        $route = new Route(
            ['GET'],
            '/',
            function (): void {
            },
            new ResponseFactory(),
            $callableResolver,
            $metadata,
            $container
        );

        $route->run($this->request);
    }

    public function testParametersTransform(): void
    {
        $callable = function ($request, $response, array $args) {
            $this->assertEquals(10, $args['id']);

            return $response;
        };
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $callableResolver->expects($this->any())
            ->method('resolve')
            ->will($this->returnValue($callable));
        /** @var CallableResolverInterface $callableResolver */
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValue(new AbstractTransformerStub(10)));
        /* @var ContainerInterface $container */

        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getTransformer')
            ->will($this->returnValue('transformer'));
        $metadata->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue(['id' => 'int']));
        /* @var RouteMetadata $metadata */

        $route = new Route(
            ['GET'],
            '/',
            $callable,
            new ResponseFactory(),
            $callableResolver,
            $metadata,
            $container
        );
        $route->setArgument('id', '10');

        $route->run($this->request);
    }

    public function testHandleResponse(): void
    {
        $callable = function ($request, $response) {
            return $response;
        };
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $callableResolver->expects($this->any())
            ->method('resolve')
            ->will($this->returnValue($callable));
        /** @var CallableResolverInterface $callableResolver */
        $route = new Route(
            ['GET'],
            '/',
            $callable,
            new ResponseFactory(),
            $callableResolver
        );

        $this->assertInstanceOf(ResponseInterface::class, $route->run($this->request));
    }
}
