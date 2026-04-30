<x-core::layouts.admin :title="__('New translation')">
    {!! BootForm::open()->action(route('admin::index-translations'))->addClass('form') !!}
    @include('admin::translations._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
