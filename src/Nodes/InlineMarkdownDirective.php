<?php

namespace Matfire\CommonMarkDirectives\Nodes;

use League\CommonMark\Node\Inline\AbstractInline;
use Matfire\CommonMarkDirectives\MarkdownComponentParameters;

final class InlineMarkdownDirective extends AbstractInline
{
    public function __construct(public readonly MarkdownComponentParameters $parameters)
    {
        parent::__construct();
    }
}
