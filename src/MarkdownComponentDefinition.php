<?php

namespace Matfire\CommonMarkDirectives;

use InvalidArgumentException;

final readonly class MarkdownComponentDefinition
{
    /** @var class-string<MarkdownComponent> */
    public string $component;

    public function __construct(
        public MarkdownDirectiveType $type,
        public string $name,
        string $component,
    ) {
        if (preg_match('/^[A-Za-z](?:[A-Za-z0-9_-]*[A-Za-z0-9])?$/', $name) !== 1) {
            throw new InvalidArgumentException("Invalid Markdown directive name [{$name}].");
        }

        if (! is_a($component, MarkdownComponent::class, true)) {
            throw new InvalidArgumentException("Markdown component [{$component}] must implement ".MarkdownComponent::class.'.');
        }

        $this->component = $component;
    }

    /** @param class-string<MarkdownComponent> $component */
    public static function text(string $name, string $component): self
    {
        return new self(MarkdownDirectiveType::Text, $name, $component);
    }

    /** @param class-string<MarkdownComponent> $component */
    public static function leaf(string $name, string $component): self
    {
        return new self(MarkdownDirectiveType::Leaf, $name, $component);
    }

    /** @param class-string<MarkdownComponent> $component */
    public static function container(string $name, string $component): self
    {
        return new self(MarkdownDirectiveType::Container, $name, $component);
    }
}
