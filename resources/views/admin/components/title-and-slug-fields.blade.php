@props(['locales' => locales()])

<div class="row gx-3">
    <div class="col-md-6">
        {!! TranslatableBootForm::text(__('Title'), 'title') !!}
    </div>
    <div class="col-md-6">
        <x-core::slug-field></x-core::slug-field>
    </div>
</div>
