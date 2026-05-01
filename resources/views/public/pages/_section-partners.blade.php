@if (($featuredPartners = TypiCMS\Modules\Partners\Models\Partner::published()->where('homepage', 1)->get()) and $featuredPartners->count() > 0)
    <div class="partner-list-home">
        <div class="partner-list-home-container">
            <div class="partner-list-home-header">
                <h2 class="partner-list-home-title">{{ __('Partners') }}</h2>
                @if (Route::has(app()->getLocale() . '::index-partners'))
                    <div class="partner-list-home-action">
                        <a class="partner-list-home-action-button" href="{{ route(app()->getLocale() . '::index-partners') }}">{{ __('All partners') }}</a>
                    </div>
                @endif
            </div>
            @include('public::partners._list', ['items' => $featuredPartners])
        </div>
    </div>
@endif
