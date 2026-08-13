<?php

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Matfire\CommonMarkDirectives\MarkdownComponent;
use Matfire\CommonMarkDirectives\MarkdownComponentDefinition;
use Matfire\CommonMarkDirectives\MarkdownComponentParameters;
use Matfire\CommonMarkDirectives\MarkdownComponentsExtension;
use Psr\Container\ContainerInterface;

it('renders text directives through registered components', function () {
    $html = markdownDirectiveTestRenderer([
        MarkdownComponentDefinition::text('badge', TestBadgeMarkdownComponent::class),
    ])->convert('Built with :badge[Laravel]{tone=red}.');

    expect((string) $html)->toContain('Built with <span data-tone="red">Laravel</span>.');
});

it('renders leaf directives with labels and normalized attributes', function () {
    $html = markdownDirectiveTestRenderer([
        MarkdownComponentDefinition::leaf('codesandbox', TestEmbedMarkdownComponent::class),
    ])->convert('::codesandbox[Demo]{projectType="devbox" #preview .wide disabled}');

    expect((string) $html)
        ->toContain('<figure id="preview" class="wide" data-project-type="devbox" data-disabled="true">Demo</figure>');
});

it('renders container directive content as markdown', function () {
    $html = markdownDirectiveTestRenderer([
        MarkdownComponentDefinition::container('callout', TestCalloutMarkdownComponent::class),
    ])->convert(<<<'MARKDOWN'
:::callout{type="warning"}
Read **carefully**.
:::
MARKDOWN);

    expect((string) $html)
        ->toContain('<aside data-type="warning">')
        ->toContain('<p>Read <strong>carefully</strong>.</p>')
        ->toContain('</aside>');
});

it('only parses a directive when its name and type are registered', function () {
    $html = markdownDirectiveTestRenderer([
        MarkdownComponentDefinition::container('callout', TestCalloutMarkdownComponent::class),
    ])->convert('::callout{type="warning"}');

    expect((string) $html)->toContain('::callout{type=&quot;warning&quot;}');
});

it('requires registered classes to implement the component contract', function () {
    expect(fn () => MarkdownComponentDefinition::leaf('embed', stdClass::class))
        ->toThrow(InvalidArgumentException::class);
});

/**
 * @param  iterable<MarkdownComponentDefinition>  $definitions
 */
function markdownDirectiveTestRenderer(iterable $definitions): MarkdownConverter
{
    $environment = new Environment;
    $environment->addExtension(new CommonMarkCoreExtension);
    $environment->addExtension(new MarkdownComponentsExtension($definitions, new TestContainer));

    return new MarkdownConverter($environment);
}

final class TestContainer implements ContainerInterface
{
    public function get(string $id): object
    {
        return new $id;
    }

    public function has(string $id): bool
    {
        return is_a($id, MarkdownComponent::class, true);
    }
}

final class TestBadgeMarkdownComponent implements MarkdownComponent
{
    public function render(MarkdownComponentParameters $parameters): string
    {
        return sprintf(
            '<span data-tone="%s">%s</span>',
            escapeHtml((string) ($parameters->attributes['tone'] ?? '')),
            escapeHtml((string) $parameters->label),
        );
    }
}

final class TestEmbedMarkdownComponent implements MarkdownComponent
{
    public function render(MarkdownComponentParameters $parameters): string
    {
        return sprintf(
            '<figure id="%s" class="%s" data-project-type="%s" data-disabled="%s">%s</figure>',
            escapeHtml((string) ($parameters->attributes['id'] ?? '')),
            escapeHtml((string) ($parameters->attributes['class'] ?? '')),
            escapeHtml((string) ($parameters->attributes['projectType'] ?? '')),
            ($parameters->attributes['disabled'] ?? false) === true ? 'true' : 'false',
            escapeHtml((string) $parameters->label),
        );
    }
}

final class TestCalloutMarkdownComponent implements MarkdownComponent
{
    public function render(MarkdownComponentParameters $parameters): string
    {
        return sprintf(
            '<aside data-type="%s">%s</aside>',
            escapeHtml((string) ($parameters->attributes['type'] ?? 'info')),
            $parameters->content,
        );
    }
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
