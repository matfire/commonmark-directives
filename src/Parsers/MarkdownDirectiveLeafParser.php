<?php

namespace Matfire\CommonMarkDirectives\Parsers;

use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use Matfire\CommonMarkDirectives\MarkdownComponentParameters;
use Matfire\CommonMarkDirectives\Nodes\BlockMarkdownDirective;

final class MarkdownDirectiveLeafParser extends AbstractBlockContinueParser
{
    private BlockMarkdownDirective $block;

    public function __construct(MarkdownComponentParameters $parameters)
    {
        $this->block = new BlockMarkdownDirective($parameters);
    }

    public function getBlock(): BlockMarkdownDirective
    {
        return $this->block;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        return BlockContinue::none();
    }
}
