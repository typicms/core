<x-core::layouts.admin :title="$model->title" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-taxonomy', $model->id))->addClass('form') !!}

    {!! BootForm::bind($model) !!}
    @include('admin::taxonomies._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
