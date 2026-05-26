<x-core::layouts.admin :title="$model->presentTitle()" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-user', $model->id))->addClass('form') !!}

    {!! BootForm::bind($model->toArray() + ['checked_roles' => $checkedRoles]) !!}
    @include('admin::users._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
