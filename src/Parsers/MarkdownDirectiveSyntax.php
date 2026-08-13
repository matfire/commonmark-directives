<?php

namespace Matfire\CommonMarkDirectives\Parsers;

use League\CommonMark\Util\RegexHelper;

final class MarkdownDirectiveSyntax
{
    /**
     * @return array{name: string, label: ?string, attributes: array<string, bool|string>, length: int}|null
     */
    public function parse(string $input, int $markerLength, bool $mustConsumeLine): ?array
    {
        $marker = str_repeat(':', $markerLength);

        if (! str_starts_with($input, $marker)) {
            return null;
        }

        $offset = $markerLength;
        $remainder = substr($input, $offset);

        if (preg_match('/^[A-Za-z](?:[A-Za-z0-9_-]*[A-Za-z0-9])?/', $remainder, $matches) !== 1) {
            return null;
        }

        $name = $matches[0];
        $offset += strlen($name);
        $nextCharacter = $this->characterAt($input, $offset);

        if ($nextCharacter !== null && preg_match('/[A-Za-z0-9_-]/', $nextCharacter) === 1) {
            return null;
        }

        $label = null;
        $hasLabel = false;
        if ($nextCharacter === '[') {
            $parsedLabel = $this->parseDelimited($input, $offset, '[', ']');
            if ($parsedLabel === null) {
                return null;
            }

            $label = $parsedLabel['value'] === '' ? null : RegexHelper::unescape($parsedLabel['value']);
            $offset = $parsedLabel['offset'];
            $hasLabel = true;
        }

        $attributes = [];
        $hasAttributes = false;
        if ($this->characterAt($input, $offset) === '{') {
            $parsedAttributes = $this->parseDelimited($input, $offset, '{', '}');
            if ($parsedAttributes === null) {
                return null;
            }

            $attributes = $this->parseAttributes($parsedAttributes['value']);
            if ($attributes === null) {
                return null;
            }

            $offset = $parsedAttributes['offset'];
            $hasAttributes = true;
        }

        if (! $hasLabel && ! $hasAttributes && $this->characterAt($input, $offset) === ':') {
            return null;
        }

        if ($mustConsumeLine && trim(substr($input, $offset)) !== '') {
            return null;
        }

        return [
            'name' => $name,
            'label' => $label,
            'attributes' => $attributes,
            'length' => $offset,
        ];
    }

    /**
     * @return array{value: string, offset: int}|null
     */
    private function parseDelimited(string $input, int $offset, string $opening, string $closing): ?array
    {
        $length = strlen($input);
        $depth = 0;
        $quote = null;
        $escaped = false;

        for ($position = $offset; $position < $length; $position++) {
            $character = $this->characterAt($input, $position);

            if ($escaped) {
                $escaped = false;

                continue;
            }

            if ($character === '\\') {
                $escaped = true;

                continue;
            }

            if ($opening === '{' && ($character === '"' || $character === "'")) {
                if ($quote === null) {
                    $quote = $character;
                } elseif ($quote === $character) {
                    $quote = null;
                }

                continue;
            }

            if ($quote !== null) {
                continue;
            }

            if ($character === $opening) {
                $depth++;

                continue;
            }

            if ($character !== $closing) {
                continue;
            }

            $depth--;
            if ($depth === 0) {
                return [
                    'value' => substr($input, $offset + 1, $position - $offset - 1),
                    'offset' => $position + 1,
                ];
            }
        }

        return null;
    }

    /**
     * @return array<string, bool|string>|null
     */
    private function parseAttributes(string $input): ?array
    {
        $attributes = [];
        $classes = [];
        $length = strlen($input);
        $offset = 0;

        while ($offset < $length) {
            $this->skipWhitespace($input, $offset);
            if ($offset >= $length) {
                break;
            }

            $character = $this->characterAt($input, $offset);
            if ($character === '#' || $character === '.') {
                $shortcut = $character;
                $offset++;
                $value = $this->readAttributeToken($input, $offset);

                if ($value === '') {
                    return null;
                }

                if ($shortcut === '#') {
                    $attributes['id'] = $value;
                } else {
                    $classes[] = $value;
                }

                continue;
            }

            $name = $this->readAttributeName($input, $offset);
            if ($name === '') {
                return null;
            }

            $this->skipWhitespace($input, $offset);
            if ($this->characterAt($input, $offset) !== '=') {
                $attributes[$name] = true;

                continue;
            }

            $offset++;
            $this->skipWhitespace($input, $offset);
            $value = $this->readAttributeValue($input, $offset);
            if ($value === null) {
                return null;
            }

            if (strtolower($name) === 'class') {
                array_push($classes, ...(preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: []));
            } else {
                $attributes[$name] = $value;
            }
        }

        if ($classes !== []) {
            $attributes['class'] = implode(' ', $classes);
        }

        return $attributes;
    }

    private function readAttributeName(string $input, int &$offset): string
    {
        $start = $offset;
        $length = strlen($input);

        while ($offset < $length) {
            $character = $this->characterAt($input, $offset);
            if ($character === null || preg_match('/[\s=]/u', $character) === 1) {
                break;
            }

            $offset++;
        }

        return substr($input, $start, $offset - $start);
    }

    private function readAttributeToken(string $input, int &$offset): string
    {
        $start = $offset;
        $length = strlen($input);

        while ($offset < $length) {
            $character = $this->characterAt($input, $offset);
            if ($character === null || preg_match('/\s/u', $character) === 1) {
                break;
            }

            $offset++;
        }

        return substr($input, $start, $offset - $start);
    }

    private function readAttributeValue(string $input, int &$offset): ?string
    {
        $quote = $this->characterAt($input, $offset);
        if ($quote !== '"' && $quote !== "'") {
            $value = $this->readAttributeToken($input, $offset);

            return $value === '' ? null : RegexHelper::unescape($value);
        }

        $offset++;
        $start = $offset;
        $length = strlen($input);
        $escaped = false;

        while ($offset < $length) {
            $character = $this->characterAt($input, $offset);

            if ($escaped) {
                $escaped = false;
                $offset++;

                continue;
            }

            if ($character === '\\') {
                $escaped = true;
                $offset++;

                continue;
            }

            if ($character === $quote) {
                $value = substr($input, $start, $offset - $start);
                $offset++;

                return RegexHelper::unescape($value);
            }

            $offset++;
        }

        return null;
    }

    private function skipWhitespace(string $input, int &$offset): void
    {
        $length = strlen($input);

        while ($offset < $length && preg_match('/\s/u', (string) $this->characterAt($input, $offset)) === 1) {
            $offset++;
        }
    }

    private function characterAt(string $input, int $offset): ?string
    {
        if ($offset >= strlen($input)) {
            return null;
        }

        return substr($input, $offset, 1);
    }
}
