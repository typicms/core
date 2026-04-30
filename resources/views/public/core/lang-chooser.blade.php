<x-core::layouts.public body-class="lang-chooser">
    <x-slot:skip-links></x-slot:skip-links>
    <x-slot:header></x-slot:header>
    <x-slot:footer></x-slot:footer>

    <div class="page-header lang-chooser-header">
        <h1 class="lang-chooser-title">Choose language</h1>
    </div>

    <ul class="lang-chooser-list">
        @foreach (enabledLocales() as $locale)
            <li class="lang-chooser-list-item">
                <a class="lang-chooser-list-anchor" href="{{ $homepage->url($locale) }}">
                    @lang('languages.' . $locale, [], $locale)
                </a>
            </li>
        @endforeach
    </ul>
</x-core::layouts.public>
