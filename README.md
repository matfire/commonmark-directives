<div align="center">
    <h1>CommonMark Directives</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/matfire/commonmark-directives"><img src="https://img.shields.io/packagist/v/matfire/commonmark-directives.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/matfire/commonmark-directives"><img src="https://img.shields.io/packagist/php-v/matfire/commonmark-directives.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://github.com/matfire/commonmark-directives/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/matfire/commonmark-directives/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/matfire/commonmark-directives"><img src="https://img.shields.io/packagist/dt/matfire/commonmark-directives.svg?style=flat-square" alt="Total Downloads"></a>
</p>

# CommonMark Directives

A [league/commonmark](https://commonmark.thephpleague.com/) extension for rendering registered text, leaf, and container directives through small PHP components.

## Installation

```bash
composer require matfire/commonmark-directives
```

## Usage

Create a component that returns the directive's HTML:

```php
use Matfire\CommonMarkDirectives\MarkdownComponent;
use Matfire\CommonMarkDirectives\MarkdownComponentParameters;

final class Badge implements MarkdownComponent
{
    public function render(MarkdownComponentParameters $parameters): string
    {
        return sprintf('<span>%s</span>', htmlspecialchars($parameters->label ?? ''));
    }
}
```

Register it with a CommonMark environment and a PSR-11 container:

```php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Matfire\CommonMarkDirectives\MarkdownComponentDefinition;
use Matfire\CommonMarkDirectives\MarkdownComponentsExtension;

$environment = new Environment;
$environment->addExtension(new CommonMarkCoreExtension);
$environment->addExtension(new MarkdownComponentsExtension([
    MarkdownComponentDefinition::text('badge', Badge::class),
], $container));

$converter = new MarkdownConverter($environment);
$html = $converter->convert('Built with :badge[Laravel]{tone=red}.');
```

The container must implement `Psr\Container\ContainerInterface` and resolve each registered component class.

## Syntax

```markdown
:badge[Laravel]{tone=red}

::youtube[Video title]{id=jjKFXlFNR4E}

:::callout{type=warning}
Container content supports **Markdown**.
:::
```

- `text()` registers inline directives beginning with `:`.
- `leaf()` registers standalone block directives beginning with `::`.
- `container()` registers fenced block directives beginning with `:::`.
- Attributes support quoted or unquoted values, boolean attributes, `#id`, and `.class` shortcuts.
- Only registered name-and-type combinations are parsed as directives.

## Testing

```bash
composer test
```

## License

MIT
