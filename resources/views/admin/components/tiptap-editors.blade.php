@props(['model', 'name', 'label' => null])

@foreach (locales() as $locale)
    <tiptap-editor name="{{ $name }}[{{ $locale }}]" locale="{{ $locale }}" init-content="{{ old("{$name}.{$locale}", $model->getTranslation($name, $locale)) }}" :label="'{{ $label }}'"></tiptap-editor>
@endforeach
