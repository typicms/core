<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Taxonomies')" :default-title="__('New taxonomy')" />

<div class="form-body">
    <x-core::form-errors />

    <x-bootform::text :label="__('Name')" name="name" required autocomplete="off" />
    <x-transbootform::text :label="__('Info for search results')" name="result_string" />

    <x-core::title-and-slug-fields />

    <x-bootform::text :label="__('Validation rule')" name="validation_rule" placeholder="required|array|size:2" required />

    <x-bootform::hidden name="modules[]" value="" />
    @if ($modules)
        <p class="form-label">{{ __('Use in modules') }}</p>
        @foreach ($modules as $module => $properties)
            <div class="form-check">
                {!! Form::checkbox('modules[]', $module)->id($module)->addClass('form-check-input') !!}
                <label class="form-check-label" for="{{ $module }}">{{ __(ucfirst($module)) }}</label>
            </div>
        @endforeach
    @endif
</div>
