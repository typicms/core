<x-core::layouts.page :page="$page">
    <x-slot:header-title>
        <h1 class="header-title"><x-core::header-title /></h1>
    </x-slot:header-title>

    <div class="page-body">
        <div class="page-body-container">
            @include('public::pages._main-content', ['page' => $page])
            @include('public::files._document-list', ['model' => $page])
            @include('public::files._image-list', ['model' => $page])
            @include('public::pages._sections', compact('page'))
            {{--
            @if (($slides = TypiCMS\Modules\Slides\Models\Slide::published()->order()->get()) and $slides->count() > 0)
                @include('public::slides._slider', ['items' => $slides])
            @endif
            --}}
        </div>
    </div>
</x-core::layouts.page>
