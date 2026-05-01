<x-core::layouts.admin :title="$model->title" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-term', [$taxonomy->id, $model->id]))->addClass('form') !!} {!! BootForm::bind($model) !!}
    @include('admin::taxonomies._form-term')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
