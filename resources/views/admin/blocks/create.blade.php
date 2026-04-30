<x-core::layouts.admin :title="__('New content block')">
    {!! BootForm::open()->action(route('admin::index-blocks'))->addClass('form') !!}
    @include('admin::blocks._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
