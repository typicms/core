<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use DOMDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

final class DashboardAdminController extends BaseAdminController
{
    public function index(): RedirectResponse
    {
        return redirect(route('admin::dashboard'));
    }

    public function dashboard(): View
    {
        $welcomeMessage = $this->fetchWelcomeMessage();

        return view('admin::dashboard.show', ['welcomeMessage' => $welcomeMessage]);
    }

    private function fetchWelcomeMessage(): string
    {
        $fallback = (string) config('typicms.welcome_message');
        $url = (string) config('typicms.welcome_message_url');

        if ($url === '' || ! $this->isAllowedUrl($url)) {
            return $fallback;
        }

        $body = rescue(fn () => Http::timeout(2)->get($url)->body());

        if ($body !== null) {
            return $this->sanitizeHtml($body);
        }

        return $fallback;
    }

    /**
     * Sanitize remotely fetched HTML before it is rendered unescaped in the
     * dashboard. Beyond limiting the allowed tags, every attribute is removed
     * so that event handlers (onclick, onerror, …) and javascript: URIs cannot
     * be smuggled in through a compromised or man-in-the-middled upstream. Only
     * a safe href is restored on links.
     */
    private function sanitizeHtml(string $html): string
    {
        $html = strip_tags($html, '<a><strong><em><br><p><ul><ol><li>');

        if (mb_trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8" ?><body>'.$html.'</body>',
            LIBXML_NOERROR | LIBXML_NOWARNING,
        );
        libxml_clear_errors();

        foreach (iterator_to_array($dom->getElementsByTagName('*')) as $element) {
            $href = mb_strtolower($element->tagName) === 'a' ? $element->getAttribute('href') : null;

            while ($element->attributes->length > 0) {
                $element->removeAttribute($element->attributes->item(0)->nodeName);
            }

            if ($href !== null && $this->isSafeHref($href)) {
                $element->setAttribute('href', $href);
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $output = '';
        if ($body) {
            foreach ($body->childNodes as $child) {
                $output .= (string) $dom->saveHTML($child);
            }
        }

        return mb_trim($output);
    }

    private function isSafeHref(string $href): bool
    {
        $href = mb_trim($href);

        if ($href === '') {
            return false;
        }

        if (str_starts_with($href, '/') || str_starts_with($href, '#')) {
            return true;
        }

        $scheme = mb_strtolower((string) parse_url($href, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }

    private function isAllowedUrl(string $url): bool
    {
        $parsed = parse_url($url);

        if (! isset($parsed['scheme'], $parsed['host'])) {
            return false;
        }

        if (! in_array($parsed['scheme'], ['http', 'https'], true)) {
            return false;
        }

        $ip = gethostbyname($parsed['host']);

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
