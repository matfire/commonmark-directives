<?php

namespace Matfire\CommonMarkDirectives\Parsers;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use Matfire\CommonMarkDirectives\MarkdownComponentParameters;
use Matfire\CommonMarkDirectives\Nodes\BlockMarkdownDirective;

final class MarkdownDirectiveContainerParser extends AbstractBlockContinueParser
{
    private BlockMarkdownDirective $block;

    public function __construct(MarkdownComponentParameters $parameters, private readonly int $markerLength)
    {
        $this->block = new BlockMarkdownDirective($parameters);
    }

    public function getBlock(): BlockMarkdownDirective
    {
        return $this->block;
    }

    public function isContainer(): bool
    {
        return true;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): BlockContinue
    {
        if (! $cursor->isIndented()) {
            $closingFence = '/^\s{0,3}:{'.$this->markerLength.',}[ \t]*$/';

            if (preg_match($closingFence, $cursor->getLine()) === 1) {
                return BlockContinue::finished();
            }
        }

        return BlockContinue::at($cursor);
    }

    public function closeBlock(): void
    {
        if (($lastChild = $this->block->lastChild()) instanceof AbstractBlock) {
            $this->block->setEndLine($lastChild->getEndLine());
        }
    }
}
