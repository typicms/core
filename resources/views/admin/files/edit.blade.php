<x-core::layouts.admin :title="$model->presentTitle()" :model="$model">
    {!! BootForm::open()->put()->action(route('admin::update-file', $model->id))->addClass('form')->multipart() !!}
    {!! BootForm::bind($model) !!}
    @include('admin::files._form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
