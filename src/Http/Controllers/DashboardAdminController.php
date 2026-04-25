<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

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
            return strip_tags($body, '<a><strong><em><br><p><ul><ol><li>');
        }

        return $fallback;
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
