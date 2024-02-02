<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Console;

use Slim\Interfaces\RouteInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Routing\RoutingResults;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'slim:routing:match')]
class MatchCommand extends AbstractRoutingCommand
{
    public function __construct(
        private RouteResolverInterface $routeResolver,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Match routing')
            ->addArgument('path', InputArgument::REQUIRED, 'Route path')
            ->addArgument('method', InputArgument::OPTIONAL, 'Route method')
            ->setHelp(<<<'HELP'
            The <info>%command.name%</info> command matches registered routes.

            Search for route match by method and path:

              <info>%command.full_name%</info> <comment>/home GET</comment>

            HELP);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $ioStyle = new SymfonyStyle($input, $output);

        /** @var string $searchPath */
        $searchPath = $input->getArgument('path');
        /** @var string|null $searchMethod */
        $searchMethod = $input->getArgument('method');

        $routingResults = array_map(
            fn(string $method): RoutingResults
                => $this->routeResolver->computeRoutingResults($searchPath, $method),
            $searchMethod !== null
                ? [mb_strtoupper($searchMethod)]
                : ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        );
        $routingResults = array_filter(
            $routingResults,
            static fn(RoutingResults $routingResult): bool
                => $routingResult->getRouteStatus() === RoutingResults::FOUND,
        );

        if (\count($routingResults) === 0) {
            $ioStyle->error('No matched routes');

            return self::FAILURE;
        }

        $routes = array_values(array_map(
            fn(RoutingResults $routingResult): RouteInterface
                => $this->routeResolver->resolveRoute($routingResult->getRouteIdentifier() ?? ''),
            $routingResults,
        ));

        $ioStyle->comment('Matched routes');

        (new Table($output))
            ->setStyle('symfony-style-guide')
            ->setHeaders(['Path', 'Methods', 'Name', 'Invokable'])
            ->setRows($this->getTableRows($routes))
            ->render();

        $ioStyle->newLine();

        return self::SUCCESS;
    }
}
