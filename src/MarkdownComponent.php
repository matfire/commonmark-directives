<?php

namespace Matfire\CommonMarkDirectives;

interface MarkdownComponent
{
    public function render(MarkdownComponentParameters $parameters): string;
}
