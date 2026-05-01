<x-core::layouts.page :$page :canonical="url()->full()" :body-class="'body-search body-search-index body-page body-page-' . $page->id">
    <div class="page-body">
        <div class="page-body-container">
            @if (!$errors->has('query'))
                <div class="search-results">
                    <h1 class="search-results-title">{{ __('Search results for “:query”', ['query' => e((string) request()->string('query'))]) }}</h1>

                    @if ($count)
                        <div class="search-results-content">
                            @foreach ($results as $key => $result)
                                <div class="search-results-module">
                                    <h2 class="search-results-module-title">
                                        {{ $result['models']->count() }}
                                        @choice($result['module'], $result['models']->count())
                                    </h2>
                                    <div class="search-results-module-results">
                                        @include('public::' . $result['module'] . '._list-results',
                                            [
                                                'items' => $result['models']
                                            ])
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="search-results-no-results text-center">{{ __('There are no results that match your query.') }}</p>
                    @endif
                </div>
            @else
                <p class="search-results-no-results text-center">{{ $errors->first('query') }}</p>
            @endif
        </div>
    </div>
</x-core::layouts.page>
