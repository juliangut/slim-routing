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

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    use PathTrait {
        PathTrait::__construct as protected pathConstruct;
    }
    use ArgumentTrait {
        ArgumentTrait::__construct as protected argumentConstruct;
    }

    protected ?string $name = null;

    protected ?string $transformer = null;

    /**
     * @var array<string>
     */
    protected array $methods = ['GET'];

    protected bool $xmlHttpRequest;

    protected int $priority;

    /**
     * @param array<string>|null         $methods
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
        ?string $transformer = null,
        ?array $placeholders = [],
        ?array $parameters = [],
        ?array $arguments = []
    ) {
        if ($name !== null) {
            $this->setName($name);
        }
        $this->transformer = $transformer;
        if ($methods !== null) {
            $this->setMethods($methods);
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
        if (mb_strpos(trim($name), ' ') !== false) {
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

    public function getTransformer(): ?string
    {
        return $this->transformer;
    }

    /**
     * @param array<mixed> $methods
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
     * @return array<string>
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
