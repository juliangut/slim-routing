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

namespace Jgut\Slim\Routing\Source;

/**
 * Abstract routing source.
 */
abstract class AbstractSource implements SourceInterface
{
    /**
     * Sources.
     *
     * @var array
     */
    protected $paths;

    /**
     * Source constructor.
     *
     * @param array|\Traversable|string $paths
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($paths)
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        if (!is_array($paths) && !$paths instanceof \Traversable) {
            throw new \InvalidArgumentException(
                sprintf('Paths must be a string or traversable, "%s" given', gettype($paths))
            );
        }

        $this->paths = $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(): array
    {
        return $this->paths;
    }
}
