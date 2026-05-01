<x-core::layouts.admin :title="$model->presentTitle()" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-page', $model->id))->addClass('form') !!} {!! BootForm::bind($model) !!}
    @include('admin::pages._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
