<?php

declare(strict_types=1);

use TypiCMS\Modules\Core\Support\HtmlSanitizer;

function clean(?string $html): string
{
    return (new HtmlSanitizer)->sanitize($html);
}

it('returns an empty string for empty input', function (): void {
    expect(clean(null))->toBe('')
        ->and(clean(''))->toBe('')
        ->and(clean('   '))->toBe('');
});

it('removes script tags', function (): void {
    expect(clean('<p>hi</p><script>alert(1)</script>'))
        ->toBe('<p>hi</p>');
});

it('strips event handlers from allowed tags', function (): void {
    expect(clean('<img src="x" onerror="alert(1)">'))
        ->not->toContain('onerror');
});

it('neutralizes javascript urls on allowed anchors', function (): void {
    expect(clean('<a href="javascript:alert(1)">click</a>'))
        ->not->toContain('javascript:')
        ->toContain('click');
});

it('keeps safe formatting markup', function (): void {
    expect(clean('<p>Welcome <strong>back</strong> <a href="https://example.com">home</a></p>'))
        ->toContain('<strong>back</strong>')
        ->toContain('href="https://example.com"');
});
