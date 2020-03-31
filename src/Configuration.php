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

use Jgut\Mapping\Driver\DriverInterface;
use Jgut\Mapping\Metadata\MetadataResolver;
use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use Jgut\Slim\Routing\Naming\SnakeCase;
use Jgut\Slim\Routing\Naming\Strategy;
use Jgut\Slim\Routing\Route\RouteResolver;

/**
 * Routing configuration.
 */
class Configuration
{
    /**
     * Routing sources.
     *
     * @var mixed[]
     */
    protected $sources = [];

    /**
     * Routes with trailing slash.
     *
     * @var bool
     */
    protected $trailingSlash = false;

    /**
     * Placeholder aliases.
     *
     * @var array<string, string>
     */
    protected $placeholderAliases = [
        'any' => '[^}]+',
        'numeric' => '[0-9]+',
        'number' => '[0-9]+',
        'alpha' => '[a-zA-Z]+',
        'word' => '[a-zA-Z]+',
        'alnum' => '[a-zA-Z0-9]+',
        'slug' => '[a-zA-Z0-9-]+',
        'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}',
        'mongoid' => '[0-9a-f]{24}',
    ];

    /**
     * Metadata resolver.
     *
     * @var MetadataResolver
     */
    protected $metadataResolver;

    /**
     * Route resolver.
     *
     * @var RouteResolver
     */
    protected $routeResolver;

    /**
     * Naming strategy.
     *
     * @var Strategy
     */
    protected $namingStrategy;

    /**
     * Configuration constructor.
     *
     * @param mixed $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configurations = [])
    {
        if (!\is_array($configurations) && !$configurations instanceof \Traversable) {
            throw new \InvalidArgumentException('Configurations must be an iterable');
        }
        if ($configurations instanceof \Traversable) {
            $configurations = \iterator_to_array($configurations);
        }

        $configs = \array_keys(\get_object_vars($this));

        $unknownParameters = \array_diff(\array_keys($configurations), $configs);
        if (\count($unknownParameters) > 0) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'The following configuration parameters are not recognized: %s',
                    \implode(', ', $unknownParameters)
                )
            );
        }

        foreach ($configs as $config) {
            if (isset($configurations[$config])) {
                $callback = [
                    $this,
                    $config === 'placeholderAliases' ? 'addPlaceholderAliases' : 'set' . \ucfirst($config),
                ];

                if (\is_callable($callback)) {
                    \call_user_func($callback, $configurations[$config]);
                }
            }
        }
    }

    /**
     * Get routing paths.
     *
     * @return mixed[]
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * Set routing paths.
     *
     * @param mixed[] $sources
     *
     * @return self
     */
    public function setSources(array $sources): self
    {
        $this->sources = [];

        foreach ($sources as $source) {
            $this->addSource($source);
        }

        return $this;
    }

    /**
     * Add mapping source.
     *
     * @param mixed $source
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function addSource($source): self
    {
        if (!\is_string($source) && !\is_array($source) && !$source instanceof DriverInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Mapping source must be a string, array or %s, %s given',
                DriverInterface::class,
                \is_object($source) ? \get_class($source) : \gettype($source)
            ));
        }

        $this->sources[] = $source;

        return $this;
    }

    /**
     * Should routes have trailing slash.
     *
     * @return bool
     */
    public function hasTrailingSlash(): bool
    {
        return $this->trailingSlash;
    }

    /**
     * Set route trailing slash selector.
     *
     * @param bool $trailingSlash
     *
     * @return $this
     */
    public function setTrailingSlash(bool $trailingSlash): self
    {
        $this->trailingSlash = $trailingSlash;

        return $this;
    }

    /**
     * Get placeholder aliases.
     *
     * @return array<string, string>
     */
    public function getPlaceholderAliases(): array
    {
        return $this->placeholderAliases;
    }

    /**
     * Add placeholder aliases.
     *
     * @param array<string, string> $aliases
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function addPlaceholderAliases(array $aliases): self
    {
        foreach ($aliases as $alias => $patter) {
            $this->addPlaceholderAlias($alias, $patter);
        }

        return $this;
    }

    /**
     * Add placeholder alias.
     *
     * @param string $alias
     * @param string $pattern
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function addPlaceholderAlias(string $alias, string $pattern): self
    {
        if (@\preg_match('~^' . $pattern . '$~', '') === false) {
            throw new \InvalidArgumentException(
                \sprintf('Placeholder pattern "%s" is not a valid regex', $pattern)
            );
        }

        $this->placeholderAliases[$alias] = $pattern;

        return $this;
    }

    /**
     * Get metadata resolver.
     *
     * @return MetadataResolver
     */
    public function getMetadataResolver(): MetadataResolver
    {
        if ($this->metadataResolver === null) {
            $this->metadataResolver = new MetadataResolver(new DriverFactory());
        }

        return $this->metadataResolver;
    }

    /**
     * Set metadata resolver.
     *
     * @param MetadataResolver $metadataResolver
     *
     * @return self
     */
    public function setMetadataResolver(MetadataResolver $metadataResolver): self
    {
        $this->metadataResolver = $metadataResolver;

        return $this;
    }

    /**
     * Get route resolver.
     *
     * @return RouteResolver
     */
    public function getRouteResolver(): RouteResolver
    {
        if ($this->routeResolver === null) {
            $this->routeResolver = new RouteResolver($this);
        }

        return $this->routeResolver;
    }

    /**
     * Set route resolver.
     *
     * @param RouteResolver $routeResolver
     *
     * @return self
     */
    public function setRouteResolver(RouteResolver $routeResolver): self
    {
        $this->routeResolver = $routeResolver;

        return $this;
    }

    /**
     * Get naming strategy.
     *
     * @return Strategy
     */
    public function getNamingStrategy(): Strategy
    {
        if ($this->namingStrategy === null) {
            $this->namingStrategy = new SnakeCase();
        }

        return $this->namingStrategy;
    }

    /**
     * Set naming strategy.
     *
     * @param Strategy $namingStrategy
     *
     * @return self
     */
    public function setNamingStrategy(Strategy $namingStrategy): self
    {
        $this->namingStrategy = $namingStrategy;

        return $this;
    }
}
