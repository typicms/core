<x-core::header :$model :back-url="$page->editUrl()" :back-label="$page->title" :default-title="__('New page section')" />

<div class="form-body">
    <x-core::form-errors />

    <div class="row">
        <div class="col-lg-8">
            <x-bootform::hidden name="id" />
            <x-bootform::hidden name="page_id" :value="$page->id" />

            <x-core::title-and-slug-fields />
            <div class="mb-3">
                <x-transbootform::checkbox :label="__('Published')" name="status" :unchecked-value="0" />
            </div>
            <div class="mb-3">
                <x-bootform::checkbox :label="__('Hide title')" name="hide_title" :unchecked-value="0" />
            </div>
            <div class="row">
                <div class="col-md-6">
                    <x-bootform::select :label="__('Template')" name="template" :options="pageSectionTemplates()" />
                </div>
            </div>
            <x-core::tiptap-editors :$model name="body" :label="__('Body')" />
        </div>
        <div class="col-lg-4">
            <div class="right-column">
                <file-manager></file-manager>
                <file-field type="image" field="image_id" :init-file="{{ $model->image ?? 'null' }}"></file-field>
                <files-field :init-files="{{ $model->files }}"></files-field>
            </div>
        </div>
    </div>
</div>
