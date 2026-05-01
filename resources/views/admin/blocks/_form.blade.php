<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Blocks')" :default-title="__('New block')" />

<file-manager></file-manager>

<div class="form-body">
    <x-core::form-errors />

    @if ($model->id)
        {!! BootForm::hidden('name') !!}
    @else
        {!! BootForm::text(__('Name'), 'name')->required()->autocomplete('off') !!}
    @endif

    <div class="mb-3">{!! TranslatableBootForm::hidden('status')->value(0) !!} {!! TranslatableBootForm::checkbox(__('Published'), 'status') !!}</div>
    <x-core::tiptap-editors :$model name="body" :label="__('Body')" />
</div>
