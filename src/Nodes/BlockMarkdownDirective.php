<?php

namespace Matfire\CommonMarkDirectives\Nodes;

use League\CommonMark\Node\Block\AbstractBlock;
use Matfire\CommonMarkDirectives\MarkdownComponentParameters;

final class BlockMarkdownDirective extends AbstractBlock
{
    public function __construct(public readonly MarkdownComponentParameters $parameters)
    {
        parent::__construct();
    }
}
