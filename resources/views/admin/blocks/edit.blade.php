<x-core::layouts.admin :title="$model->presentTitle()" :model="$model">
    {!! BootForm::open()->put()->action(route('admin::update-block', $model->id))->addClass('form') !!}
    {!! BootForm::bind($model) !!}
    @include('admin::blocks._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
