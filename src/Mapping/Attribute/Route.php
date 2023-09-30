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

use Attribute;
use Jgut\Mapping\Exception\AttributeException;
use Jgut\Slim\Routing\Transformer\ParameterTransformer;

#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    use PathTrait {
        PathTrait::__construct as protected pathConstruct;
    }
    use ArgumentTrait {
        ArgumentTrait::__construct as protected argumentConstruct;
    }

    protected ?string $name = null;

    /**
     * @var list<string>
     */
    protected array $methods = ['GET'];

    /**
     * @var list<string|object>|null
     */
    protected ?array $transformers = null;

    protected bool $xmlHttpRequest;

    protected int $priority;

    /**
     * @param list<string>|null          $methods
     * @param list<string|object>|null   $transformers
     * @param array<string, string>|null $placeholders
     * @param array<string, string>|null $parameters
     * @param array<string, string>|null $arguments
     */
    public function __construct(
        ?string $pattern = null,
        ?array $methods = [],
        ?string $name = null,
        ?bool $xmlHttpRequest = false,
        ?int $priority = 0,
        ?array $transformers = null,
        ?array $placeholders = [],
        ?array $parameters = [],
        ?array $arguments = [],
    ) {
        if ($name !== null) {
            $this->setName($name);
        }
        if ($methods !== null) {
            $this->setMethods($methods);
        }
        if ($transformers !== null) {
            $this->setTransformers($transformers);
        }
        $this->xmlHttpRequest = $xmlHttpRequest ?? false;
        $this->priority = $priority ?? 0;

        $this->pathConstruct($pattern, $placeholders, $parameters);
        $this->argumentConstruct($arguments);
    }

    /**
     * @throws AttributeException
     */
    protected function setName(string $name): void
    {
        if (str_contains(trim($name), ' ')) {
            throw new AttributeException('Route name must not contain spaces.');
        }

        if (trim($name) === '') {
            throw new AttributeException('Route name can not be empty.');
        }

        $this->name = trim($name);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param list<mixed> $methods
     *
     * @throws AttributeException
     */
    protected function setMethods(array $methods): void
    {
        if (\count($methods) === 0) {
            return;
        }

        foreach (array_filter($methods) as $method) {
            if (!\is_string($method)) {
                throw new AttributeException(
                    sprintf('Route annotation methods must be strings. "%s" given.', \gettype($method)),
                );
            }

            $this->methods[] = mb_strtoupper(trim($method));
        }

        $this->methods = array_values(array_unique(array_filter($this->methods, 'strlen')));

        if (\count($this->methods) === 0) {
            throw new AttributeException('Route annotation methods can not be empty.');
        }

        if (\in_array('ANY', $this->methods, true) && \count($this->methods) > 1) {
            throw new AttributeException('Route "ANY" method cannot be defined with other methods.');
        }
    }

    /**
     * @return list<string|object>|null
     */
    public function getTransformers(): ?array
    {
        return $this->transformers;
    }

    /**
     * @param list<string|object> $transformers
     *
     * @throws AttributeException
     */
    protected function setTransformers(array $transformers): void
    {
        foreach ($transformers as $transformer) {
            if (!\is_string($transformer) && !$transformer instanceof ParameterTransformer) {
                throw new AttributeException(sprintf(
                    'Route transformers must be a list of string or "%s". "%s" given.',
                    ParameterTransformer::class,
                    $transformer::class,
                ));
            }
        }

        $this->transformers = array_values($transformers);
    }

    /**
     * @return list<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function isXmlHttpRequest(): bool
    {
        return $this->xmlHttpRequest;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
