@props(['locales' => locales()])

@foreach ($locales as $locale)
    {!!
        BootForm::inputGroup(__('Slug') . ' <span>(' . $locale . ')</span>', 'slug[' . $locale . ']')
            ->attribute('data-slug', 'title[' . $locale . ']')
            ->data('language', $locale)
            ->addGroupClass('form-group-translation')
            ->afterAddon(BootForm::button(__('Generate'))->class('btn btn-outline-secondary btn-slug'))
    !!}
@endforeach
