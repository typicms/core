{!! BootForm::hidden('id') !!}

<x-core::header :$model :backUrl="$model->indexUrl()" :backLabel="__('Roles')" :defaultTitle="__('New role')" :lang-switcher="false" />

<div class="form-body">
    <div class="row gx-3">
        <div class="col-sm-6">
            {!! BootForm::text(__('Name'), 'name')->required()->autocomplete('off') !!}
        </div>
    </div>

    <x-core::permissions-form />
</div>
