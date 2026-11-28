<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Services;

use Illuminate\Http\Request;
use Spatie\MarkdownResponse\Actions\DetectsMarkdownRequest;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

class CacheProfile extends CacheAllSuccessfulGetRequests
{
    public function useCacheNameSuffix(Request $request): string
    {
        $suffix = parent::useCacheNameSuffix($request);

        if (resolve(DetectsMarkdownRequest::class)($request) !== null) {
            $suffix .= '-markdown';
        }

        return $suffix;
    }
}
