<x-core::layouts.page
    :page="$page"
    :body-class="'body-tags body-tags-index body-page body-page-' . $page->id"
>
    <div class="page-body">
        <div class="page-body-container">
            @include('public::pages._main-content', ['page' => $page])
            @include('public::files._document-list', ['model' => $page])
            @include('public::files._image-list', ['model' => $page])

            @if ($models->count() > 0)
                @include('public::tags._list', ['items' => $models])
            @endif

            {!! $models->appends(Request::except('page'))->links() !!}
        </div>
    </div>
</x-core::layouts.page>
