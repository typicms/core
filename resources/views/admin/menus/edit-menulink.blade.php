<x-core::layouts.admin :title="$model->title" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-menulink', [$menu->id, $model->id]))->addClass('form') !!}

    {!! BootForm::bind($model) !!}
    @include('admin::menus._form-menulink')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
