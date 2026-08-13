<?php

namespace Matfire\CommonMarkDirectives;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use Matfire\CommonMarkDirectives\Nodes\BlockMarkdownDirective;
use Matfire\CommonMarkDirectives\Nodes\InlineMarkdownDirective;
use Matfire\CommonMarkDirectives\Parsers\MarkdownDirectiveBlockStartParser;
use Matfire\CommonMarkDirectives\Parsers\MarkdownDirectiveSyntax;
use Matfire\CommonMarkDirectives\Parsers\MarkdownDirectiveTextParser;
use Matfire\CommonMarkDirectives\Renderers\MarkdownDirectiveRenderer;
use Psr\Container\ContainerInterface;

final class MarkdownComponentsExtension implements ExtensionInterface
{
    private MarkdownComponentRegistry $registry;

    /** @param iterable<MarkdownComponentDefinition> $components */
    public function __construct(
        iterable $components,
        private readonly ContainerInterface $container,
    ) {
        $this->registry = new MarkdownComponentRegistry($components);
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $syntax = new MarkdownDirectiveSyntax;
        $renderer = new MarkdownDirectiveRenderer($this->registry, $this->container);

        $environment
            ->addBlockStartParser(new MarkdownDirectiveBlockStartParser($this->registry, $syntax))
            ->addInlineParser(new MarkdownDirectiveTextParser($this->registry, $syntax))
            ->addRenderer(BlockMarkdownDirective::class, $renderer)
            ->addRenderer(InlineMarkdownDirective::class, $renderer);
    }
}
