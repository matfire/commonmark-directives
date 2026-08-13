<?php

namespace Matfire\CommonMarkDirectives\Parsers;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use Matfire\CommonMarkDirectives\MarkdownComponentParameters;
use Matfire\CommonMarkDirectives\MarkdownComponentRegistry;
use Matfire\CommonMarkDirectives\MarkdownDirectiveType;

final readonly class MarkdownDirectiveBlockStartParser implements BlockStartParserInterface
{
    public function __construct(
        private MarkdownComponentRegistry $registry,
        private MarkdownDirectiveSyntax $syntax,
    ) {}

    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented() || $parserState->getParagraphContent() !== null || ! $parserState->getActiveBlockParser()->isContainer()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $remainder = $cursor->getRemainder();
        $markerLength = strspn($remainder, ':');

        $type = match (true) {
            $markerLength === 2 => MarkdownDirectiveType::Leaf,
            $markerLength >= 3 => MarkdownDirectiveType::Container,
            default => null,
        };

        if ($type === null) {
            return BlockStart::none();
        }

        $parsed = $this->syntax->parse($remainder, $markerLength, true);
        if ($parsed === null || $this->registry->find($type, $parsed['name']) === null) {
            return BlockStart::none();
        }

        $cursor->advanceBy($parsed['length']);
        $parameters = new MarkdownComponentParameters(
            type: $type,
            name: $parsed['name'],
            label: $parsed['label'],
            attributes: $parsed['attributes'],
        );

        $blockParser = $type === MarkdownDirectiveType::Container
            ? new MarkdownDirectiveContainerParser($parameters, $markerLength)
            : new MarkdownDirectiveLeafParser($parameters);

        return BlockStart::of($blockParser)->at($cursor);
    }
}
