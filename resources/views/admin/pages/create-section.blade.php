<x-core::layouts.admin :title="__('New page section')" :page="$page">
    {!! BootForm::open()->action(route('admin::store-page_section', $page->id))->addClass('form') !!}
    @include('admin::pages._form-section')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
