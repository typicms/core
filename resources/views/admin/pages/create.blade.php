<x-core::layouts.admin :title="__('New page')">
    {!! BootForm::open()->action(route('admin::index-pages'))->addClass('form') !!}
    @include('admin::pages._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
