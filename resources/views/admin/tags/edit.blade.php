<x-core::layouts.admin :title="$model->presentTitle()" :$model>
    {!! BootForm::open()->put()->action(route('admin::update-tag', $model->id))->addClass('form') !!} {!! BootForm::bind($model) !!}
    @include('admin::tags._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
