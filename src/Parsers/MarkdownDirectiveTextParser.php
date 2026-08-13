<?php

namespace Matfire\CommonMarkDirectives\Parsers;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Matfire\CommonMarkDirectives\MarkdownComponentParameters;
use Matfire\CommonMarkDirectives\MarkdownComponentRegistry;
use Matfire\CommonMarkDirectives\MarkdownDirectiveType;
use Matfire\CommonMarkDirectives\Nodes\InlineMarkdownDirective;

final readonly class MarkdownDirectiveTextParser implements InlineParserInterface
{
    public function __construct(
        private MarkdownComponentRegistry $registry,
        private MarkdownDirectiveSyntax $syntax,
    ) {}

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string(':');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();

        if ($cursor->peek() === ':') {
            return false;
        }

        $parsed = $this->syntax->parse($cursor->getRemainder(), 1, false);

        if ($parsed === null || $this->registry->find(MarkdownDirectiveType::Text, $parsed['name']) === null) {
            return false;
        }

        $cursor->advanceBy($parsed['length']);
        $inlineContext->getContainer()->appendChild(new InlineMarkdownDirective(
            new MarkdownComponentParameters(
                type: MarkdownDirectiveType::Text,
                name: $parsed['name'],
                label: $parsed['label'],
                attributes: $parsed['attributes'],
            ),
        ));

        return true;
    }
}
