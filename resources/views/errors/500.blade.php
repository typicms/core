<x-core::layouts.public :title="__('Something went wrong') . ' – ' . websiteTitle()" body-class="error-500">
    <x-slot:lang-switcher></x-slot:lang-switcher>

    <header class="page-header">
        <div class="page-header-container">
            <h1 class="page-title">{{ __('Something went wrong') }}</h1>
        </div>
    </header>

    <div class="page-body">
        <div class="page-body-container">
            <p>{{ __('We hit an unexpected error on our end. Please try again in a moment.') }}</p>
        </div>
    </div>
</x-core::layouts.public>
