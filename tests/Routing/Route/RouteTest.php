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
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Interfaces\CallableResolverInterface;
use stdClass;

/**
 * @internal
 */
class RouteTest extends TestCase
{
    protected ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->request = (new ServerRequestFactory())->createServerRequest('GET', '/');
    }

    public function testNonXmlHttpRequestRequest(): void
    {
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(static::once())
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $route = new Route(
            ['GET'],
            '/',
            static function (): void {
            },
            new ResponseFactory(),
            $callableResolver,
            $metadata,
        );

        static::assertEquals($metadata, $route->getMetadata());

        $response = $route->run($this->request);

        static::assertEquals(400, $response->getStatusCode());
    }

    public function testWrongParameterTransformer(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/^Parameter transformer should implement .+\\\ParameterTransformer, ".+" given\.$/',
        );

        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->method('get')
            ->willReturn(new stdClass());

        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(static::once())
            ->method('getTransformer')
            ->willReturn('transformer');

        $route = new Route(
            ['GET'],
            '/',
            static function (): void {
            },
            new ResponseFactory(),
            $callableResolver,
            $metadata,
            $container,
        );

        $route->run($this->request);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testParametersTransform(): void
    {
        $callable = static function ($request, $response, array $args) {
            static::assertEquals(10, $args['id']);

            return $response;
        };
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $callableResolver
            ->method('resolve')
            ->willReturn($callable);
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->method('get')
            ->willReturn(new AbstractTransformerStub(10));

        $group = $this->getMockBuilder(GroupMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $group->expects(static::once())
            ->method('getParameters')
            ->willReturn([]);

        $metadata = $this->getMockBuilder(RouteMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects(static::once())
            ->method('getTransformer')
            ->willReturn('transformer');
        $metadata->expects(static::once())
            ->method('getParameters')
            ->willReturn(['id' => 'int']);
        $metadata->expects(static::once())
            ->method('getGroupChain')
            ->willReturn([$group]);

        $route = new Route(
            ['GET'],
            '/',
            $callable,
            new ResponseFactory(),
            $callableResolver,
            $metadata,
            $container,
        );
        $route->setArgument('id', '10');

        $route->run($this->request);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testHandleResponse(): void
    {
        $callable = static fn($request, $response) => $response;
        $callableResolver = $this->getMockBuilder(CallableResolverInterface::class)
            ->getMock();
        $callableResolver
            ->method('resolve')
            ->willReturn($callable);
        /** @var CallableResolverInterface $callableResolver */
        $route = new Route(
            ['GET'],
            '/',
            $callable,
            new ResponseFactory(),
            $callableResolver,
        );

        static::assertInstanceOf(ResponseInterface::class, $route->run($this->request));
    }
}
