<x-core::layouts.admin :title="$model->presentTitle()" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-translation', $model->id))->addClass('form') !!} {!! BootForm::bind($model) !!}
    @include('admin::translations._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
