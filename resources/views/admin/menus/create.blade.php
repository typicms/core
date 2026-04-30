<x-core::layouts.admin :title="__('New menu')">
    {!! BootForm::open()->action(route('admin::index-menus'))->addClass('form') !!}
    @include('admin::menus._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
