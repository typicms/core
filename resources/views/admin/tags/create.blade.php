<x-core::layouts.admin :title="__('New tag')">
    {!! BootForm::open()->action(route('admin::index-tags'))->addClass('form') !!}
    @include('admin::tags._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
