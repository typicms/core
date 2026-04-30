<x-core::layouts.admin :title="__('New menulink')">
    {!! BootForm::open()->action(route('admin::store-menulink', $menu->id))->addClass('form') !!}
    @include('admin::menus._form-menulink')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
