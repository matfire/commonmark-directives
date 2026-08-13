<?php

namespace Matfire\CommonMarkDirectives;

final readonly class MarkdownComponentParameters
{
    /**
     * @param  array<string, bool|string>  $attributes
     */
    public function __construct(
        public MarkdownDirectiveType $type,
        public string $name,
        public ?string $label,
        public array $attributes,
        public string $content = '',
    ) {}

    public function withContent(string $content): self
    {
        return new self(
            type: $this->type,
            name: $this->name,
            label: $this->label,
            attributes: $this->attributes,
            content: $content,
        );
    }
}
