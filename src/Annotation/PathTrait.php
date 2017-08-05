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

namespace Jgut\Slim\Routing\Annotation;

/**
 * Path annotation trait.
 */
trait PathTrait
{
    /**
     * Pattern path.
     *
     * @var string
     */
    protected $pattern = '/';

    /**
     * Pattern path placeholders regex.
     *
     * @var array
     */
    protected $placeholders = [];

    /**
     * Get pattern path.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Set pattern path.
     *
     * @param string $pattern
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setPattern(string $pattern)
    {
        if (preg_match('/\{.+:(.+)?\}/', $pattern, $matches)) {
            throw new \InvalidArgumentException(
                sprintf('Placeholder matching "%s" must be defined on placeholders parameter', $matches[1])
            );
        }

        $this->pattern = '/' . trim($pattern, '/ ');

        return $this;
    }

    /**
     * Get pattern placeholders regex.
     *
     * @return array
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * Set pattern placeholders regex.
     *
     * @param array $placeholders
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;

        return $this;
    }
}
