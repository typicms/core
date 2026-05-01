@if (Route::has(app()->getLocale() . '::search'))
    <form class="search-form" method="get" action="{{ route(app()->getLocale() . '::search') }}">
        <div class="input-group">
            <input class="search-input form-control" type="text" name="query" id="query" aria-label="{{ __('Search') }}" placeholder="{{ __('Search') }}" value="{{ request()->string('query') }}" />
            <button class="search-button btn btn-primary" type="submit">Search</button>
        </div>
    </form>
@endif
