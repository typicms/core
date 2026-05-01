<x-core::layouts.page :$page>
    <div class="page-body">
        <div class="page-body-container">
            @include('public::pages._subpages')
            @include('public::pages._main-content', ['page' => $page])
            @include('public::files._document-list', ['model' => $page])
            @include('public::files._image-list', ['model' => $page])
        </div>

        @include('public::pages._sections', compact('page'))
    </div>
</x-core::layouts.page>
