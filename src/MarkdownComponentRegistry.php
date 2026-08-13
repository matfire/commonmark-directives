<?php

namespace Matfire\CommonMarkDirectives;

use InvalidArgumentException;

final class MarkdownComponentRegistry
{
    /** @var array<string, MarkdownComponentDefinition> */
    private array $definitions = [];

    /** @param iterable<MarkdownComponentDefinition> $definitions */
    public function __construct(iterable $definitions)
    {
        foreach ($definitions as $definition) {
            $key = $this->key($definition->type, $definition->name);

            if (isset($this->definitions[$key])) {
                throw new InvalidArgumentException("Markdown directive [{$key}] is registered more than once.");
            }

            $this->definitions[$key] = $definition;
        }
    }

    public function find(MarkdownDirectiveType $type, string $name): ?MarkdownComponentDefinition
    {
        return $this->definitions[$this->key($type, $name)] ?? null;
    }

    private function key(MarkdownDirectiveType $type, string $name): string
    {
        return "{$type->value}:{$name}";
    }
}
