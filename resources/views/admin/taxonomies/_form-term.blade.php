<x-core::header :$model :back-url="route('admin::index-terms', $taxonomy)" :back-label="__('Terms')" :default-title="__('New term')" :lang-switcher="false" />

<div class="form-body">
    <x-core::form-errors />
    <x-core::title-and-slug-fields />
</div>
