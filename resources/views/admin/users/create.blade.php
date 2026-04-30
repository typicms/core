<x-core::layouts.admin :title="__('New user')">
    {!! BootForm::open()->action(route('admin::index-users'))->addClass('form') !!}
    @include('admin::users._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
