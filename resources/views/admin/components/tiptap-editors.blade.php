@props(['model', 'name', 'label' => null])

@foreach (locales() as $locale)
    <tiptap-editor
        name="{{ $name }}[{{ $locale }}]"
        locale="{{ $locale }}"
        init-content="{{ old("{$name}.{$locale}", $model->getTranslation($name, $locale)) }}"
        :label="'{{ $label }}'"
        :text-align="false"
        :heading-levels="[2, 3, 4, 5]"
    ></tiptap-editor>
@endforeach
