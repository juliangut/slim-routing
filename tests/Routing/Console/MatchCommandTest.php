<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Console;

use Jgut\Slim\Routing\Console\MatchCommand;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\Tests\Stubs\ConsoleOutputStub;
use Laminas\Diactoros\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\CallableResolver;
use Slim\Routing\RouteResolver;
use Slim\Routing\RoutingResults;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * @internal
 */
class MatchCommandTest extends TestCase
{
    public function testNoRoutes(): void
    {
        $routingResults = $this->getMockBuilder(RoutingResults::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routingResults->expects(static::once())
            ->method('getRouteStatus')
            ->willReturn(RoutingResults::NOT_FOUND);

        $routeResolver = $this->getMockBuilder(RouteResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeResolver->expects(static::once())
            ->method('computeRoutingResults')
            ->willReturn($routingResults);

        $command = new MatchCommand($routeResolver);

        $input = new ArgvInput([MatchCommand::getDefaultName(), '/home', 'get'], $command->getDefinition());

        $output = new ConsoleOutputStub();

        static::assertSame(1, $command->execute($input, $output));
        static::assertStringContainsString('No matched routes', $output->getOutput());
    }

    /**
     * @dataProvider provideSearchRegexCases
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testSearchRegex(Route $route, ?string $method, string $path): void
    {
        $noRoutingResult = $this->getMockBuilder(RoutingResults::class)
            ->disableOriginalConstructor()
            ->getMock();
        $noRoutingResult->method('getRouteStatus')
            ->willReturn(RoutingResults::NOT_FOUND);

        $routingResult = $this->getMockBuilder(RoutingResults::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routingResult->method('getRouteStatus')
            ->willReturn(RoutingResults::FOUND);
        $routingResult->method('getRouteIdentifier')
            ->willReturn('1');

        $routeResolver = $this->getMockBuilder(RouteResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeResolver->method('computeRoutingResults')
            ->willReturnCallback(
                static function (
                    string $resolvePath,
                    string $resolveMethod,
                ) use (
                    $route,
                    $routingResult,
                    $noRoutingResult
                ) {
                    return \in_array($resolveMethod, $route->getMethods(), true)
                        ? $routingResult
                        : $noRoutingResult;
                },
            );
        $routeResolver->method('resolveRoute')
            ->willReturn($route);

        $command = new MatchCommand($routeResolver);

        $input = $method !== null
            ? new ArgvInput([MatchCommand::getDefaultName(), $path, $method], $command->getDefinition())
            : new ArgvInput([MatchCommand::getDefaultName(), $path], $command->getDefinition());

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));
        static::assertStringContainsString($path, $output->getOutput());

        foreach ($route->getMethods() as $requestedMethod) {
            static::assertStringContainsString(mb_strtoupper($requestedMethod), $output->getOutput());
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function provideSearchRegexCases(): iterable
    {
        $container = new class () implements ContainerInterface {
            public function get(string $name)
            {
                return $name;
            }

            public function has(string $name): bool
            {
                return false;
            }
        };

        $responseFactory = new ResponseFactory();
        $callableResolver = new CallableResolver($container);

        $routeHome = new Route(
            ['GET'],
            '/home',
            ['invokable', 'method'],
            $responseFactory,
            $callableResolver,
        );
        $routeProfile = new Route(
            ['GET', 'POST'],
            '/profile',
            'callable',
            $responseFactory,
            $callableResolver,
        );

        yield [$routeHome, 'get', '/home'];
        yield [$routeHome, null, '/home'];
        yield [$routeProfile, 'post', '/profile'];
        yield [$routeProfile, null, '/profile'];
    }
}
