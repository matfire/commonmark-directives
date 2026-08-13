<?php

namespace Matfire\CommonMarkDirectives\Renderers;

use InvalidArgumentException;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use LogicException;
use Matfire\CommonMarkDirectives\MarkdownComponent;
use Matfire\CommonMarkDirectives\MarkdownComponentRegistry;
use Matfire\CommonMarkDirectives\MarkdownDirectiveType;
use Matfire\CommonMarkDirectives\Nodes\BlockMarkdownDirective;
use Matfire\CommonMarkDirectives\Nodes\InlineMarkdownDirective;
use Psr\Container\ContainerInterface;

final readonly class MarkdownDirectiveRenderer implements NodeRendererInterface
{
    public function __construct(
        private MarkdownComponentRegistry $registry,
        private ContainerInterface $container,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof BlockMarkdownDirective && ! $node instanceof InlineMarkdownDirective) {
            throw new InvalidArgumentException('Incompatible node type: '.get_debug_type($node));
        }

        $parameters = $node->parameters;
        if ($node instanceof BlockMarkdownDirective && $parameters->type === MarkdownDirectiveType::Container) {
            $parameters = $parameters->withContent($childRenderer->renderNodes($node->children()));
        }

        $definition = $this->registry->find($parameters->type, $parameters->name)
            ?? throw new LogicException("Markdown directive [{$parameters->type->value}:{$parameters->name}] is not registered.");

        $component = $this->container->get($definition->component);
        if (! $component instanceof MarkdownComponent) {
            throw new LogicException("Markdown component [{$definition->component}] could not be resolved.");
        }

        return $component->render($parameters);
    }
}
