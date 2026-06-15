<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Support;

use DOMAttr;
use DOMComment;
use DOMElement;
use DOMNode;

/**
 * Allowlist based sanitizer for rich text (TipTap) HTML.
 *
 * It walks an already parsed DOM tree and strips everything that is not
 * explicitly allowed: unknown elements, event handler attributes, dangerous
 * URL protocols and unsafe inline styles. This neutralizes stored XSS while
 * preserving the markup the TipTap editor produces (links, images, tables,
 * figures, media embeds, …).
 */
class HtmlSanitizer
{
    /**
     * Elements that are removed together with their content.
     *
     * @var list<string>
     */
    private const REMOVE_WITH_CONTENT = [
        'script', 'style', 'noscript', 'template', 'object', 'embed', 'applet',
        'form', 'input', 'button', 'select', 'option', 'textarea', 'label',
        'fieldset', 'link', 'meta', 'base', 'head', 'title', 'frame', 'frameset',
        'svg', 'math', 'canvas', 'audio', 'video', 'source', 'track', 'param',
        'map', 'area', 'dialog', 'slot', 'xml',
    ];

    /**
     * Allowed elements mapped to their element specific attributes.
     * Global attributes (see GLOBAL_ATTRIBUTES) are allowed everywhere.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED_ELEMENTS = [
        'p' => [],
        'div' => [],
        'span' => [],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'br' => [], 'hr' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'strike' => [], 'del' => [], 'ins' => [], 'sub' => [], 'sup' => [],
        'mark' => [], 'small' => [], 'abbr' => [], 'code' => [], 'pre' => [],
        'kbd' => [], 'samp' => [], 'var' => [], 'q' => [], 'cite' => [],
        'blockquote' => [],
        'ul' => [], 'ol' => ['start', 'type'], 'li' => [],
        'dl' => [], 'dt' => [], 'dd' => [],
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height'],
        'figure' => [], 'figcaption' => [],
        'iframe' => ['src', 'width', 'height', 'allowfullscreen', 'allow', 'loading', 'frameborder', 'referrerpolicy'],
        'table' => [], 'thead' => [], 'tbody' => [], 'tfoot' => [], 'tr' => [], 'caption' => [],
        'th' => ['colspan', 'rowspan', 'scope'],
        'td' => ['colspan', 'rowspan'],
        'colgroup' => ['span'], 'col' => ['span'],
    ];

    /**
     * Attributes allowed on every element.
     *
     * @var list<string>
     */
    private const GLOBAL_ATTRIBUTES = ['class', 'style', 'dir', 'lang', 'title'];

    /**
     * Attributes whose value is treated as a URL.
     *
     * @var list<string>
     */
    private const URL_ATTRIBUTES = ['href', 'src'];

    /**
     * URL schemes allowed for regular links.
     *
     * @var list<string>
     */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel', 'ftp'];

    /**
     * CSS properties allowed in inline styles.
     *
     * @var list<string>
     */
    private const ALLOWED_STYLE_PROPERTIES = [
        'text-align', 'vertical-align', 'width', 'height', 'min-width', 'max-width',
    ];

    public function sanitizeNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->sanitizeChild($child);
        }
    }

    private function sanitizeChild(DOMNode $node): void
    {
        if ($node instanceof DOMComment) {
            $node->parentNode?->removeChild($node);

            return;
        }

        if (! $node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->localName ?? $node->nodeName);

        if (in_array($tag, self::REMOVE_WITH_CONTENT, true)) {
            $node->parentNode?->removeChild($node);

            return;
        }

        if (! array_key_exists($tag, self::ALLOWED_ELEMENTS)) {
            $this->sanitizeNode($node);
            $this->unwrap($node);

            return;
        }

        $this->cleanAttributes($node, $tag);

        if ($tag === 'iframe' && ! $node->hasAttribute('src')) {
            $node->parentNode?->removeChild($node);

            return;
        }

        $this->sanitizeNode($node);
    }

    private function unwrap(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if ($parent === null) {
            return;
        }

        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = array_merge(self::GLOBAL_ATTRIBUTES, self::ALLOWED_ELEMENTS[$tag]);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (! $attribute instanceof DOMAttr) {
                continue;
            }

            $name = strtolower($attribute->nodeName);

            if (str_starts_with($name, 'on')) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if (str_starts_with($name, 'data-')) {
                continue;
            }

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if ($name === 'style') {
                $style = $this->sanitizeStyle($attribute->value);
                if ($style === '') {
                    $element->removeAttribute($attribute->nodeName);

                    continue;
                }

                $element->setAttribute($attribute->nodeName, $style);

                continue;
            }

            if (in_array($name, self::URL_ATTRIBUTES, true) && ! $this->isSafeUrl($attribute->value, $tag)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }
    }

    private function isSafeUrl(string $url, string $tag): bool
    {
        $normalized = strtolower((string) preg_replace('/[\s\x00-\x1F\x7F]+/', '', $url));

        if ($tag === 'iframe') {
            return str_starts_with($normalized, 'https://')
                || str_starts_with($normalized, 'http://')
                || str_starts_with($normalized, '//');
        }

        if (str_starts_with($normalized, 'data:')) {
            return $tag === 'img' && str_starts_with($normalized, 'data:image/');
        }

        if (preg_match('/^([a-z][a-z0-9+.\-]*):/', $normalized, $matches) === 1) {
            return in_array($matches[1], self::ALLOWED_SCHEMES, true);
        }

        return true;
    }

    private function sanitizeStyle(string $style): string
    {
        $declarations = [];

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = explode(':', $declaration, 2);
            $property = strtolower(trim($property));
            $value = trim($value);

            if (! in_array($property, self::ALLOWED_STYLE_PROPERTIES, true)) {
                continue;
            }

            if ($value === '' || preg_match('/^[a-z0-9\s%#.,\-]+$/i', $value) !== 1) {
                continue;
            }

            $declarations[] = "{$property}: {$value}";
        }

        return implode('; ', $declarations);
    }
}
