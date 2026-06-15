<?php

declare(strict_types=1);

use TypiCMS\Modules\Core\Observers\TipTapHTMLObserver;

function patch(string $html): string
{
    return (string) (new TipTapHTMLObserver)->patchTipTapHTML($html, 'en');
}

it('removes script elements', function (): void {
    expect(patch('<p>ok</p><script>document.body.dataset.pwn=1</script>'))
        ->not->toContain('<script')
        ->toContain('<p>ok</p>');
});

it('strips event handler attributes', function (): void {
    expect(patch('<img src="x" onerror="alert(1)">'))
        ->toContain('<img')
        ->not->toContain('onerror');
});

it('strips event handlers regardless of case', function (): void {
    expect(patch('<img src="x" OnError="alert(1)">'))
        ->not->toContain('alert(1)');
});

it('removes svg with event handlers', function (): void {
    expect(patch('<svg onload="alert(1)"></svg>'))
        ->not->toContain('onload')
        ->not->toContain('<svg');
});

it('neutralizes javascript urls in links', function (): void {
    expect(patch('<a href="javascript:alert(1)">x</a>'))
        ->not->toContain('javascript:')
        ->toContain('x');
});

it('neutralizes javascript urls obfuscated with whitespace', function (): void {
    expect(patch("<a href=\"java\tscript:alert(1)\">x</a>"))
        ->not->toContain('script:alert');
});

it('removes iframes with javascript sources', function (): void {
    expect(patch('<div data-media-embed=""><iframe src="javascript:alert(1)"></iframe></div>'))
        ->not->toContain('javascript:')
        ->not->toContain('<iframe');
});

it('removes dangerous data uris', function (): void {
    expect(patch('<a href="data:text/html,<script>alert(1)</script>">x</a>'))
        ->not->toContain('data:text/html');
});

it('preserves safe links', function (): void {
    expect(patch('<p><a href="https://example.com" target="_blank" rel="noopener">link</a></p>'))
        ->toContain('href="https://example.com"')
        ->toContain('target="_blank"');
});

it('preserves relative links', function (): void {
    expect(patch('<p><a href="/en/about">about</a></p>'))
        ->toContain('href="/en/about"');
});

it('preserves safe images including alt text', function (): void {
    expect(patch('<img src="/uploads/photo.jpg" alt="A photo" width="600" height="400">'))
        ->toContain('src="/uploads/photo.jpg"')
        ->toContain('alt="A photo"');
});

it('preserves https iframe embeds and their data wrapper', function (): void {
    $html = '<div data-media-embed=""><iframe src="https://www.youtube.com/embed/abc" width="560" height="315" allowfullscreen="true" loading="lazy"></iframe></div>';

    expect(patch($html))
        ->toContain('<iframe')
        ->toContain('src="https://www.youtube.com/embed/abc"')
        ->toContain('data-media-embed');
});

it('keeps safe text alignment styles', function (): void {
    expect(patch('<p style="text-align: center">centered</p>'))
        ->toContain('text-align: center');
});

it('drops unsafe inline styles', function (): void {
    expect(patch('<p style="background: url(javascript:alert(1))">x</p>'))
        ->not->toContain('javascript')
        ->not->toContain('url(');
});

it('preserves tables and figures', function (): void {
    $html = '<figure><img src="/a.jpg" alt="a"><figcaption>cap</figcaption></figure>'
        .'<table><tbody><tr><td colspan="2">cell</td></tr></tbody></table>';

    expect(patch($html))
        ->toContain('<figure>')
        ->toContain('<figcaption>cap</figcaption>')
        ->toContain('<table>')
        ->toContain('colspan="2"');
});

it('still flattens paragraphs inside list items', function (): void {
    expect(patch('<ul><li><p>item</p></li></ul>'))
        ->toContain('<li>item</li>');
});
