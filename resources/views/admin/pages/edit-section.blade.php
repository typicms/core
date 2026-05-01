<x-core::layouts.admin :title="$model->presentTitle()" :$model :$page>
    {!! BootForm::open()->put()->action(route('admin::update-page_section', [$page->id, $model->id]))->addClass('form') !!} {!! BootForm::bind($model) !!}
    @include('admin::pages._form-section')
    {!! BootForm::close() !!}
</x-core::layouts.admin>
