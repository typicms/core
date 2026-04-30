<x-core::layouts.admin :title="__('New taxonomy')">
    {!! BootForm::open()->action(route('admin::index-taxonomies'))->addClass('form') !!}
    @include('admin::taxonomies._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
