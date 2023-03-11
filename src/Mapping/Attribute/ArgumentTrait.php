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

namespace Jgut\Slim\Routing\Mapping\Attribute;

trait ArgumentTrait
{
    /**
     * @var array<string, string>
     */
    protected array $arguments = [];

    /**
     * @param array<string, string>|null $arguments
     */
    public function __construct(?array $arguments = [])
    {
        $this->arguments = $arguments ?? [];
    }

    /**
     * @return array<string, string>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
