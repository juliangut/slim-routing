<?php

/*
 * (c) 2017-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/slim-routing
 */

declare(strict_types=1);

namespace Jgut\Slim\Routing\Console;

use Jgut\Slim\Routing\Route\Route;
use Slim\Interfaces\RouteInterface;
use Symfony\Component\Console\Command\Command;

abstract class AbstractRoutingCommand extends Command
{
    /**
     * Get routes formatted for table.
     *
     * @param list<RouteInterface> $routes
     *
     * @return list<list<string|null>>
     */
    final protected function getTableRows(array $routes): array
    {
        return array_values(array_map(
            static function (RouteInterface $route): array {
                $xmlHttpRequest = false;
                if ($route instanceof Route) {
                    $metadata = $route->getMetadata();
                    $xmlHttpRequest = $metadata !== null ? $metadata->isXmlHttpRequest() : false;
                }

                $callable = $route->getCallable();
                if (\is_object($callable)) {
                    $callable = $callable::class . '::__invoke';
                } elseif (\is_array($callable)) {
                    $callable = implode('::', $callable);
                }

                /** @var string $callable */
                return [
                    $route->getPattern(),
                    ($xmlHttpRequest ? 'XmlHttpRequest ' : '') . implode('|', $route->getMethods()),
                    $route->getName(),
                    $callable,
                ];
            },
            $routes,
        ));
    }
}
