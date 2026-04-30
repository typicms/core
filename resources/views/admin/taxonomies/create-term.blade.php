<x-core::layouts.admin :title="__('New term')">
    {!! BootForm::open()->action(route('admin::index-terms', $taxonomy->id))->addClass('form') !!}
    @include('admin::taxonomies._form-term')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
