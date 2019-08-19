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

use Jgut\Slim\Routing\Mapping\Metadata\GroupMetadata;
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
        $metadata->expects(static::once())
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

        static::assertEquals($metadata, $route->getMetadata());

        $response = $route->run($this->request);

        static::assertEquals(400, $response->getStatusCode());
    }

    public function testWrongParameterTransformer(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp(
            '/^Parameter transformer should implement .+\\\ParameterTransformer, ".+" given$/'
        );

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        /** @var CallableResolverInterface $callableResolver */
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(static::any())
            ->method('get')
            ->will($this->returnValue(new \stdClass()));
        /* @var ContainerInterface $container */

        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(static::once())
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
            static::assertEquals(10, $args['id']);

            return $response;
        };
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $callableResolver->expects(static::any())
            ->method('resolve')
            ->will($this->returnValue($callable));
        /** @var CallableResolverInterface $callableResolver */
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects(static::any())
            ->method('get')
            ->will($this->returnValue(new AbstractTransformerStub(10)));
        /* @var ContainerInterface $container */

        $group = $this->getMockBuilder(GroupMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $group->expects(static::once())
            ->method('getParameters')
            ->will($this->returnValue([]));
        /* @var GroupMetadata $group */

        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(static::once())
            ->method('getTransformer')
            ->will($this->returnValue('transformer'));
        $metadata->expects(static::once())
            ->method('getParameters')
            ->will($this->returnValue(['id' => 'int']));
        $metadata->expects(static::once())
            ->method('getGroupChain')
            ->will($this->returnValue([$group]));
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
        $callableResolver->expects(static::any())
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

        static::assertInstanceOf(ResponseInterface::class, $route->run($this->request));
    }
}
