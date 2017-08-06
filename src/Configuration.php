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

namespace Jgut\Slim\Routing;

/**
 * Routing configuration.
 */
class Configuration
{
    /**
     * Routing sources.
     *
     * @var array
     */
    protected $sources = [];

    /**
     * Placeholder aliases.
     *
     * @var array
     */
    protected $placeholderAliases = [
        'numeric' => '\d+',
        'alpha' => '[a-zA-Z]+',
        'alnum' => '[a-zA-Z0-9]+',
        'any' => '.+',
    ];

    /**
     * Configuration constructor.
     *
     * @param array|\Traversable $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configurations = [])
    {
        if (!is_iterable($configurations)) {
            throw new \InvalidArgumentException('Configurations must be an iterable');
        }

        $this->seedConfigurations($configurations);
    }

    /**
     * Seed configurations.
     *
     * @param array|\Traversable $configurations
     */
    protected function seedConfigurations($configurations)
    {
        $configs = array_keys(get_object_vars($this));

        foreach ($configs as $config) {
            if (isset($configurations[$config])) {
                switch ($config) {
                    case 'sources':
                        $this->setSources($configurations[$config]);
                        break;

                    case 'placeholderAliases':
                        $this->addPlaceholderAliases($configurations[$config]);
                        break;
                }
            }
        }
    }

    /**
     * Get routing paths.
     *
     * @return array
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * Set routing paths.
     *
     * @param array $sources
     *
     * @return $this
     */
    public function setSources(array $sources)
    {
        $this->sources = $sources;

        return $this;
    }

    /**
     * Get placeholder aliases.
     *
     * @return array
     */
    public function getPlaceholderAliases(): array
    {
        return $this->placeholderAliases;
    }

    /**
     * Add placeholder aliases.
     *
     * @param array $aliases
     *
     * @throws \InvalidArgumentException
     */
    public function addPlaceholderAliases(array $aliases)
    {
        foreach ($aliases as $alias => $patter) {
            $this->addPlaceholderAlias($alias, $patter);
        }
    }

    /**
     * Add placeholder alias.
     *
     * @param string $alias
     * @param string $pattern
     *
     * @throws \InvalidArgumentException
     */
    public function addPlaceholderAlias(string $alias, string $pattern)
    {
        if (@preg_match('~^' . $pattern . '$~', '') === false) {
            throw new \InvalidArgumentException(
                sprintf('Placeholder pattern "%s" is not a valid regex', $pattern)
            );
        }

        $this->placeholderAliases[$alias] = $pattern;
    }
}
