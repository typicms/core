@if (($latestNews = TypiCMS\Modules\News\Models\News::query()->published()->with('image')->order()->take(3)->get()) and $latestNews->count() > 0)
    <div class="news-list-home">
        <div class="news-list-home-container">
            <div class="news-list-home-header">
                <h2 class="news-list-home-title">@lang('Latest news')</h2>
                <div class="news-list-home-action">
                    <a class="news-list-home-action-button" href="{{ route(app()->getLocale() . '::index-news') }}">@lang('All news')</a>
                </div>
            </div>
            @include('public::news._list', ['items' => $latestNews])
        </div>
    </div>
@endif
