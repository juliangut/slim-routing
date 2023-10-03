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

namespace Jgut\Slim\Routing\Console;

use Jgut\Slim\Routing\Route\Route;
use Jgut\Slim\Routing\RouteCollector;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends AbstractRoutingCommand
{
    public const NAME = 'slim:routing:list';
    private const SORT_PRIORITY = 'priority';
    private const SORT_PATH = 'path';
    private const SORT_NAME = 'name';

    protected static $defaultName = self::NAME;

    public function __construct(
        private RouteCollector $routeCollector,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('List routing')
            ->addOption(
                'sort',
                null,
                InputOption::VALUE_OPTIONAL,
                'Route sorting: path, name or priority',
                self::SORT_PRIORITY,
            )
            ->addOption('reverse', null, InputOption::VALUE_NONE, 'Reverse sorting')
            ->addArgument('search', InputArgument::OPTIONAL, 'Route search pattern')
            ->setHelp(<<<'HELP'
            The <info>%command.name%</info> command lists registered routes.

            You can search for routes by a pattern:

              <info>%command.full_name%</info> <comment>/^home/</comment>

            Results can be sorted by "path", "name" or "priority":

              <info>%command.full_name%</info> <comment>--sort=name</comment>

            Results order can be reversed:

              <info>%command.full_name%</info> <comment>--reverse</comment>

            HELP);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $ioStyle = new SymfonyStyle($input, $output);

        $routes = $this->getRoutes($input);
        if (\count($routes) === 0) {
            $ioStyle->error('No routes to show');

            return self::FAILURE;
        }

        /** @var string $sorting */
        $sorting = $input->getOption('sort');
        if (!\in_array($sorting, [self::SORT_PRIORITY, self::SORT_PATH, self::SORT_NAME], true)) {
            $ioStyle->error(sprintf('Unsupported sorting type "%s"', $sorting));

            return self::FAILURE;
        }

        if (\in_array($sorting, [self::SORT_PATH, self::SORT_NAME], true)) {
            $sortCallback = $sorting === self::SORT_NAME
                ? static fn(Route $routeA, Route $routeB): int => $routeA->getName() <=> $routeB->getName()
                : static fn(Route $routeA, Route $routeB): int => $routeA->getPattern() <=> $routeB->getPattern();

            usort($routes, $sortCallback);
        }

        if ($input->getOption('reverse') !== false) {
            $routes = array_reverse($routes);
        }

        $ioStyle->comment('List of defined routes');

        (new Table($output))
            ->setStyle('symfony-style-guide')
            ->setHeaders(['Path', 'Methods', 'Name', 'Invokable'])
            ->setRows($this->getTableRows($routes))
            ->render();

        $ioStyle->newLine();

        return self::SUCCESS;
    }

    /**
     * @return list<Route>
     */
    public function getRoutes(InputInterface $input): array
    {
        /** @var list<Route> $routes */
        $routes = $this->routeCollector->getRoutes();

        $searchPattern = $this->getSearchPattern($input);
        if ($searchPattern === null) {
            return $routes;
        }

        return array_values(array_filter(
            $routes,
            static function (Route $route) use ($searchPattern): bool {
                return preg_match($searchPattern, $route->getPattern()) === 1
                    || ($route->getName() !== null && preg_match($searchPattern, $route->getName()) === 1);
            },
        ));
    }

    private function getSearchPattern(InputInterface $input): ?string
    {
        /** @var string|null $searchPattern */
        $searchPattern = $input->getArgument('search');
        if ($searchPattern === null) {
            return null;
        }

        foreach (['~', '!', '\/', '#', '%', '\|'] as $delimiter) {
            $pattern = sprintf('/^%1$s.*%1$s[imsxeuADSUXJ]*$/', $delimiter);
            if (preg_match($pattern, $searchPattern) === 1) {
                return $searchPattern;
            }
        }

        return sprintf('/%s/i', preg_quote($searchPattern, '/'));
    }
}
