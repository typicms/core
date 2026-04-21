<x-core::header :$model :backUrl="route('admin::index-terms', $taxonomy)" :backLabel="__('Terms')" :lang-switcher="false" :defaultTitle="__('New term')" />

<div class="form-body">
    <x-core::form-errors />
    <x-core::title-and-slug-fields />
</div>
