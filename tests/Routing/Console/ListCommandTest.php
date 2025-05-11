<?php

/*
 * (c) 2017-2025 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Tests\Console;

use Jgut\Slim\Routing\Console\ListCommand;
use Jgut\Slim\Routing\Mapping\Metadata\RouteMetadata;
use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\RouteCollector;
use Jgut\Slim\Routing\Tests\Stubs\ConsoleOutputStub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * @internal
 */
class ListCommandTest extends TestCase
{
    public function testNoRoutes(): void
    {
        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects(static::once())
            ->method('getRoutes')
            ->willReturn([]);

        $command = new ListCommand($routeCollector);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', '/|#|~|%|!');

        $output = new ConsoleOutputStub();

        static::assertSame(1, $command->execute($input, $output));
        static::assertStringContainsString('No routes to show', $output->getOutput());
    }

    public function testSearchRegex(): void
    {
        $routes = $this->getMockedRoutes();

        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects(static::once())
            ->method('getRoutes')
            ->willReturn($routes);

        $command = new ListCommand($routeCollector);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', '/a/i');

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));
        static::assertStringContainsString('RouteA', $output->getOutput());
        static::assertStringNotContainsString('RouteB', $output->getOutput());
        static::assertStringNotContainsString('RouteC', $output->getOutput());
    }

    public function testSearchString(): void
    {
        $routes = $this->getMockedRoutes();

        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects(static::once())
            ->method('getRoutes')
            ->willReturn($routes);

        $command = new ListCommand($routeCollector);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setArgument('search', 'B');

        $output = new ConsoleOutputStub();

        static::assertSame(0, $command->execute($input, $output));
        static::assertStringContainsString('RouteB', $output->getOutput());
        static::assertStringNotContainsString('RouteA', $output->getOutput());
        static::assertStringNotContainsString('RouteC', $output->getOutput());
    }

    public function testSortPriority(): void
    {
        $routes = $this->getMockedRoutes();

        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects(static::once())
            ->method('getRoutes')
            ->willReturn($routes);

        $command = new ListCommand($routeCollector);

        $input = new ArgvInput([], $command->getDefinition());

        $output = new ConsoleOutputStub();

        $command->execute($input, $output);

        static::assertMatchesRegularExpression('/ +RouteB.+\n +RouteA.+\n +RouteC/', $output->getOutput());
    }

    public function testSortPath(): void
    {
        $routes = $this->getMockedRoutes();

        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects(static::once())
            ->method('getRoutes')
            ->willReturn($routes);

        $command = new ListCommand($routeCollector);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setOption('sort', 'path');

        $output = new ConsoleOutputStub();

        $command->execute($input, $output);

        static::assertMatchesRegularExpression('/ +RouteA.+\n +RouteB.+\n +RouteC/', $output->getOutput());
    }

    public function testSortName(): void
    {
        $routes = $this->getMockedRoutes();

        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects(static::once())
            ->method('getRoutes')
            ->willReturn($routes);

        $command = new ListCommand($routeCollector);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setOption('sort', 'name');

        $output = new ConsoleOutputStub();

        $command->execute($input, $output);

        static::assertMatchesRegularExpression('/ +RouteC.+\n +RouteA.+\n +RouteB/', $output->getOutput());
    }

    public function testReverseSort(): void
    {
        $routes = $this->getMockedRoutes();

        $routeCollector = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeCollector->expects(static::once())
            ->method('getRoutes')
            ->willReturn($routes);

        $command = new ListCommand($routeCollector);

        $input = new ArgvInput([], $command->getDefinition());
        $input->setOption('sort', 'path');
        $input->setOption('reverse', true);

        $output = new ConsoleOutputStub();

        $command->execute($input, $output);

        static::assertMatchesRegularExpression('/ +RouteC.+\n +RouteB.+\n +RouteA/', $output->getOutput());
    }

    /**
     * @return list<Route>
     */
    private function getMockedRoutes(): array
    {
        $routeA = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeA->method('getPattern')
            ->willReturn('RouteA');
        $routeA->method('getMethods')
            ->willReturn(['GET']);
        $routeA->method('getCallable')
            ->willReturn(['invokableA', 'method']);
        $routeA->method('getName')
            ->willReturn('Route Three');

        $routeB = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeB->method('getPattern')
            ->willReturn('RouteB');
        $routeB->method('getMethods')
            ->willReturn(['POST', 'PUT']);
        $routeB->method('getCallable')
            ->willReturn('invokableB');
        $routeB->method('getName')
            ->willReturn('Route Two');
        $routeB->method('getMetadata')
            ->willReturn((new RouteMetadata('callable'))
                ->setXmlHttpRequest(true));

        $routeC = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeC->method('getPattern')
            ->willReturn('RouteC');
        $routeC->method('getMethods')
            ->willReturn(['DELETE']);
        $routeC->method('getCallable')
            ->willReturn(new stdClass());
        $routeC->method('getName')
            ->willReturn('Route One');

        return [$routeB, $routeA, $routeC];
    }
}
