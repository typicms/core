<x-core::layouts.admin :title="__('New role')">
    {!! BootForm::open()->action(route('admin::index-roles'))->addClass('form') !!}
    @include('admin::roles._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
