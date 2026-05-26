<x-core::layouts.admin :title="$model->presentTitle()" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-role', $model->id))->addClass('form') !!}

    {!! BootForm::bind($model->toArray() + ['checked_permissions' => $checkedPermissions]) !!}
    @include('admin::roles._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
