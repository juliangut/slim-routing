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
use Jgut\Slim\Routing\Naming\NamingInterface;
use Jgut\Slim\Routing\Naming\SnakeCase;
use Jgut\Slim\Routing\Response\Handler\ResponseTypeHandlerInterface;
use Jgut\Slim\Routing\Route\Resolver;

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
     * Metadata resolver.
     *
     * @var MetadataResolver
     */
    protected $metadataResolver;

    /**
     * Route resolver.
     *
     * @var Resolver
     */
    protected $routeResolver;

    /**
     * Naming strategy.
     *
     * @var NamingInterface
     */
    protected $namingStrategy;

    /**
     * Response handlers.
     *
     * @var ResponseTypeHandlerInterface[]|string[]
     */
    protected $responseHandlers = [];

    /**
     * Configuration constructor.
     *
     * @param array|\Traversable $configurations
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($configurations = [])
    {
        if (!\is_iterable($configurations)) {
            throw new \InvalidArgumentException('Configurations must be an iterable');
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

                \call_user_func($callback, $configurations[$config]);
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
     * @return static
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
     * @param string|mixed[]|DriverInterface[] $source
     *
     * @throws \InvalidArgumentException
     *
     * @return static
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
     *
     * @return static
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
     * @return static
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
     * @return static
     */
    public function setMetadataResolver(MetadataResolver $metadataResolver): self
    {
        $this->metadataResolver = $metadataResolver;

        return $this;
    }

    /**
     * Get route resolver.
     *
     * @return Resolver
     */
    public function getRouteResolver(): Resolver
    {
        if ($this->routeResolver === null) {
            $this->routeResolver = new Resolver($this);
        }

        return $this->routeResolver;
    }

    /**
     * Set route resolver.
     *
     * @param Resolver $routeResolver
     *
     * @return static
     */
    public function setRouteResolver(Resolver $routeResolver): self
    {
        $this->routeResolver = $routeResolver;

        return $this;
    }

    /**
     * Get naming strategy.
     *
     * @return NamingInterface
     */
    public function getNamingStrategy(): NamingInterface
    {
        if ($this->namingStrategy === null) {
            $this->namingStrategy = new SnakeCase();
        }

        return $this->namingStrategy;
    }

    /**
     * Set naming strategy.
     *
     * @param NamingInterface $namingStrategy
     *
     * @return static
     */
    public function setNamingStrategy(NamingInterface $namingStrategy): self
    {
        $this->namingStrategy = $namingStrategy;

        return $this;
    }

    /**
     * Get response handlers.
     *
     * @return ResponseTypeHandlerInterface[]|string[]
     */
    public function getResponseHandlers(): array
    {
        return $this->responseHandlers;
    }

    /**
     * Set response handlers.
     *
     * @param array $handlers
     *
     * @return static
     */
    public function setResponseHandlers(array $handlers): self
    {
        $this->responseHandlers = [];

        foreach ($handlers as $responseType => $responseHandler) {
            $this->addResponseHandler($responseType, $responseHandler);
        }

        return $this;
    }

    /**
     * Add response handler.
     *
     * @param string                              $response
     * @param ResponseTypeHandlerInterface|string $handler
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    public function addResponseHandler(string $response, $handler): self
    {
        $this->responseHandlers[$response] = $handler;

        return $this;
    }
}
