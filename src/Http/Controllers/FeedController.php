<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Controllers;

use Spatie\Feed\Feed;
use Spatie\MarkdownResponse\Attributes\DoNotProvideMarkdown;

final class FeedController
{
    #[DoNotProvideMarkdown]
    public function __invoke(string $module): Feed
    {
        abort_unless(config('typicms.modules.'.$module.'.has_feed', false), 404);

        $page = getPageLinkedToModule($module);
        abort_if(is_null($page), 404);

        $model = resolve(config('typicms.modules.'.$module.'.model'));
        $items = $model->published()->order()->take(10)->get();

        $feed = [
            'title' => websiteTitle().' – '.$page->title,
            'description' => strip_tags($page->body ?? ''),
            'language' => localeAndRegion('-'),
            'image' => $page->image ? $page->image->render(1200, 630) : null,
        ];

        return new Feed(
            $feed['title'],
            $items,
            request()->url(),
            'feed::atom',
            $feed['description'],
            $feed['language'] ?? 'en-US',
            $feed['image'] ?? '',
            'atom',
        );
    }
}
