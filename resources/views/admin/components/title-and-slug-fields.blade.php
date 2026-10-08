@props(['locales' => locales()])

<div class="row gx-5">
    <div class="md:col-6">
        <x-transbootform::text :label="__('Title')" name="title" />
    </div>
    <div class="md:col-6">
        <x-core::slug-field></x-core::slug-field>
    </div>
</div>
