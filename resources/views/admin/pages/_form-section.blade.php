<x-core::header :$model :back-url="$page->editUrl()" :back-label="$page->title" :default-title="__('New page section')" />

<div class="form-body">
    <x-core::form-errors />

    <div class="row">
        <div class="col-lg-8">
            {!! BootForm::hidden('id') !!}
            {!! BootForm::hidden('page_id')->value($page->id) !!}

            <x-core::title-and-slug-fields />
            <div class="mb-3">
                {!! TranslatableBootForm::hidden('status')->value(0) !!}
                {!! TranslatableBootForm::checkbox(__('Published'), 'status') !!}
            </div>
            <div class="mb-3">
                {!! BootForm::hidden('hide_title')->value(0) !!}
                {!! BootForm::checkbox(__('Hide title'), 'hide_title') !!}
            </div>
            <div class="row">
                <div class="col-md-6">
                    {!! BootForm::select(__('Template'), 'template', pageSectionTemplates()) !!}
                </div>
            </div>
            <x-core::tiptap-editors :model="$model" name="body" :label="__('Body')" />
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
