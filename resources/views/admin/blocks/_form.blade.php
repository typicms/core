<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Blocks')" :default-title="__('New block')" />

<file-manager></file-manager>

<div class="form-body">
    <x-core::form-errors />

    @if ($model->id)
        <x-bootform::hidden name="name" />
    @else
        <x-bootform::text :label="__('Name')" name="name" required autocomplete="off" />
    @endif

    <div class="mb-3">
        <x-transbootform::checkbox :label="__('Published')" name="status" :unchecked-value="0" />
    </div>
    <x-core::tiptap-editors :$model name="body" :label="__('Body')" />
</div>
