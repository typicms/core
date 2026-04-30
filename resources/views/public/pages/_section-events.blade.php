@if (($upcomingEvents = new TypiCMS\Modules\Events\Models\Event()->upcoming()) and $upcomingEvents->count() > 0)
    <div class="events-list-home">
        <div class="events-list-home-container">
            <div class="events-list-home-header">
                <h2 class="events-list-home-title">@lang('Upcoming events')</h2>
                <div class="events-list-home-action">
                    <a class="events-list-home-action-button" href="{{ route(app()->getLocale() . '::index-events') }}">@lang('All events')</a>
                </div>
            </div>
            @include('public::events._list', ['items' => $upcomingEvents])
        </div>
    </div>
@endif
