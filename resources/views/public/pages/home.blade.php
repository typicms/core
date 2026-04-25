@extends('public::pages.master')

@section('header-title')
    <h1 class="header-title"><x-core::header-title /></h1>
@endsection

@section('page')
    <div class="page-body">
        <div class="page-body-container">
            @if ($page->image)
                <img class="page-image" src="{{ $page->image->render(2000) }}" width="{{ $page->image->width }}" height="{{ $page->image->height }}" alt="" />
            @endif

            @if ($page->body)
                <div class="rich-content">{!! $page->formattedBody() !!}</div>
            @endif

            @include('public::files._document-list', ['model' => $page])
            @include('public::files._image-list', ['model' => $page])
            {{--
            @if ($slides = TypiCMS\Modules\Slides\Models\Slide::published()->order()->get() and $slides->count() > 0)
                @include('public::slides._slider', ['items' => $slides])
            @endif
            --}}
            {{--
            @if ($latestNews = TypiCMS\Modules\News\Models\News::published()->order()->take(3)->get() and $latestNews->count() > 0)
                <div class="news-list-container">
                    <h3 class="news-list-title">
                        <a href="{{ Route::has(app()->getLocale() . '::index-news') ? route(app()->getLocale() . '::index-news') : '/' }}">@lang('Latest news')</a>
                    </h3>
                    @include('public::news._list', ['items' => $latestNews])
                    <a class="news-list-btn-more btn btn-light btn-sm" href="{{ Route::has(app()->getLocale() . '::index-news') ? route(app()->getLocale() . '::index-news') : '/' }}">@lang('All news')</a>
                </div>
            @endif
            --}}
            {{--
            @if ($upcomingEvents = (new TypiCMS\Modules\Events\Models\Event())->upcoming() and $upcomingEvents->count() > 0)
                <div class="event-list-container">
                    <h3 class="event-list-title">
                        <a href="{{ Route::has(app()->getLocale() . '::index-events') ? route(app()->getLocale() . '::index-events') : '/' }}">@lang('Upcoming events')</a>
                    </h3>
                    @include('public::events._list', ['items' => $upcomingEvents])
                    <a class="event-list-btn-more btn btn-light btn-sm" href="{{ Route::has(app()->getLocale() . '::index-events') ? route(app()->getLocale() . '::index-events') : '/' }}">@lang('All events')</a>
                </div>
            @endif
            --}}

            {{--
            @if ($partners = TypiCMS\Modules\Partners\Models\Partner::published()->where('homepage', 1)->get() and $partners->count() > 0)
                <div class="partner-list-container">
                    <h3 class="partner-list-title">
                        <a href="{{ Route::has(app()->getLocale() . '::index-partners') ? route(app()->getLocale() . '::index-partners') : '/' }}">@lang('Partners')</a>
                    </h3>
                    @include('public::partners._list', ['items' => $partners])
                    <a class="partner-list-btn-more btn btn-light btn-sm" href="{{ Route::has(app()->getLocale() . '::index-partners') ? route(app()->getLocale() . '::index-partners') : '/' }}">@lang('All partners')</a>
                </div>
            @endif
            --}}

        </div>
    </div>
@endsection
