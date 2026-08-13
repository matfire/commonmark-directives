<?php

namespace Matfire\CommonMarkDirectives;

enum MarkdownDirectiveType: string
{
    case Text = 'text';
    case Leaf = 'leaf';
    case Container = 'container';
}
