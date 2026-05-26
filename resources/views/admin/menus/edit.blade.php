<x-core::layouts.admin :title="$model->presentTitle()" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-menu', $model->id))->addClass('form') !!}

    {!! BootForm::bind($model) !!}
    @include('admin::menus._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
