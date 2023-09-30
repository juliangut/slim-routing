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
final class Route
{
    use PathTrait;
    use ArgumentTrait;

    protected ?string $name = null;

    /**
     * @var list<string>
     */
    protected array $methods = ['GET'];

    /**
     * @param list<string>|null          $methods
     * @param array<string, string>|null $placeholders
     * @param array<string, string>|null $arguments
     */
    public function __construct(
        ?string $name = null,
        ?array $methods = [],
        ?string $pattern = null,
        ?array $placeholders = [],
        ?array $arguments = [],
        protected bool $xmlHttpRequest = false,
        protected int $priority = 0,
    ) {
        if ($name !== null) {
            $this->setName($name);
        }
        if ($methods !== null) {
            $this->setMethods($methods);
        }
        if ($pattern !== null) {
            $this->setPattern($pattern);
        }
        if ($placeholders !== null) {
            $this->setPlaceholders($placeholders);
        }
        if ($arguments !== null) {
            $this->setArguments($arguments);
        }
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
