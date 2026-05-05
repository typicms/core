@props(['locales' => locales()])

@foreach ($locales as $locale)
    <x-bootform::input-group
        :label="__('Slug') . ' <span>(' . $locale . ')</span>'"
        :name="'slug[' . $locale . ']'"
        :data-slug="'title[' . $locale . ']'"
        :data-language="$locale"
        group-class="form-group-translation"
    >
        <x-slot:after-addon>
            <x-bootform::button :value="__('Generate')" type="btn-outline-secondary" class="btn-slug" />
        </x-slot:after-addon>
    </x-bootform::input-group>
@endforeach
