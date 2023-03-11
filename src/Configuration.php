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

use InvalidArgumentException;
use Jgut\Mapping\Driver\DriverInterface;
use Jgut\Mapping\Metadata\MetadataResolver;
use Jgut\Slim\Routing\Mapping\Driver\DriverFactory;
use Jgut\Slim\Routing\Naming\SnakeCase;
use Jgut\Slim\Routing\Naming\Strategy;
use Jgut\Slim\Routing\Route\RouteResolver;
use Traversable;

/**
 * @phpstan-type Source string|array{driver?: string|DriverInterface, type?: string, path?: string|array<string>}
 */
class Configuration
{
    /**
     * @var array<Source>
     */
    protected array $sources = [];

    protected bool $trailingSlash = false;

    /**
     * @var array<string, string>
     */
    protected array $placeholderAliases = [
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

    protected ?MetadataResolver $metadataResolver = null;

    protected ?RouteResolver $routeResolver = null;

    protected ?Strategy $namingStrategy = null;

    /**
     * @param iterable<string, mixed> $configurations
     *
     * @throws InvalidArgumentException
     */
    public function __construct(iterable $configurations = [])
    {
        if ($configurations instanceof Traversable) {
            $configurations = iterator_to_array($configurations);
        }

        $configs = array_keys(get_object_vars($this));

        $unknownParameters = array_diff(array_keys($configurations), $configs);
        if (\count($unknownParameters) > 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'The following configuration parameters are not recognized: %s.',
                    implode(', ', $unknownParameters),
                ),
            );
        }

        foreach ($configs as $config) {
            if (\array_key_exists($config, $configurations)) {
                $callback = [
                    $this,
                    $config === 'placeholderAliases' ? 'addPlaceholderAliases' : 'set' . ucfirst($config),
                ];

                if (\is_callable($callback)) {
                    $callback($configurations[$config]);
                }
            }
        }
    }

    /**
     * @return array<string|array{driver?: string|DriverInterface, type?: string, path?: string|array<string>}>
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @param array<Source> $sources
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
     * @param Source|mixed $source
     *
     * @throws InvalidArgumentException
     */
    public function addSource($source): self
    {
        if (!\is_string($source) && !\is_array($source) && !$source instanceof DriverInterface) {
            throw new InvalidArgumentException(sprintf(
                'Mapping source must be a string, array or %s, %s given.',
                DriverInterface::class,
                \is_object($source) ? \get_class($source) : \gettype($source),
            ));
        }

        /** @var string|array{driver?: string|DriverInterface, type?: string, path?: string|array<string>} $source */
        $this->sources[] = $source;

        return $this;
    }

    public function hasTrailingSlash(): bool
    {
        return $this->trailingSlash;
    }

    public function setTrailingSlash(bool $trailingSlash): self
    {
        $this->trailingSlash = $trailingSlash;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getPlaceholderAliases(): array
    {
        return $this->placeholderAliases;
    }

    /**
     * @param array<string, string> $aliases
     *
     * @throws InvalidArgumentException
     */
    public function addPlaceholderAliases(array $aliases): self
    {
        foreach ($aliases as $alias => $patter) {
            $this->addPlaceholderAlias($alias, $patter);
        }

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addPlaceholderAlias(string $alias, string $pattern): self
    {
        if (@preg_match('~^' . $pattern . '$~', '') === false) {
            throw new InvalidArgumentException(
                sprintf('Placeholder pattern "%s" is not a valid regex.', $pattern),
            );
        }

        $this->placeholderAliases[$alias] = $pattern;

        return $this;
    }

    public function getMetadataResolver(): MetadataResolver
    {
        if ($this->metadataResolver === null) {
            $this->metadataResolver = new MetadataResolver(new DriverFactory());
        }

        return $this->metadataResolver;
    }

    public function setMetadataResolver(MetadataResolver $metadataResolver): self
    {
        $this->metadataResolver = $metadataResolver;

        return $this;
    }

    public function getRouteResolver(): RouteResolver
    {
        if ($this->routeResolver === null) {
            $this->routeResolver = new RouteResolver($this);
        }

        return $this->routeResolver;
    }

    public function setRouteResolver(RouteResolver $routeResolver): self
    {
        $this->routeResolver = $routeResolver;

        return $this;
    }

    public function getNamingStrategy(): Strategy
    {
        if ($this->namingStrategy === null) {
            $this->namingStrategy = new SnakeCase();
        }

        return $this->namingStrategy;
    }

    public function setNamingStrategy(Strategy $namingStrategy): self
    {
        $this->namingStrategy = $namingStrategy;

        return $this;
    }
}
