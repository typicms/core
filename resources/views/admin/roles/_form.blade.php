<x-bootform::hidden name="id" />

<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Roles')" :default-title="__('New role')" :lang-switcher="false" />

<div class="form-body">
    <div class="row gx-3">
        <div class="col-sm-6">
            <x-bootform::text :label="__('Name')" name="name" required autocomplete="off" />
        </div>
    </div>

    <x-core::permissions-form />
</div>
