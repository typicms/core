<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Taxonomies')" :default-title="__('New taxonomy')" />

<div class="form-body">
    <x-core::form-errors />

    {!! BootForm::text(__('Name'), 'name')->required()->autocomplete('off') !!}
    {!! TranslatableBootForm::text(__('Info for search results'), 'result_string') !!}

    <x-core::title-and-slug-fields />

    {!! BootForm::text(__('Validation rule'), 'validation_rule')->placeholder('required|array|size:2')->required() !!}

    {!! Form::hidden('modules[]')->value('') !!}
    @if ($modules)
        <p class="form-label">@lang('Use in modules')</p>
        @foreach ($modules as $module => $properties)
            <div class="form-check">
                {!! Form::checkbox('modules[]', $module)->id($module)->addClass('form-check-input') !!}
                <label class="form-check-label" for="{{ $module }}">@lang(ucfirst($module))</label>
            </div>
        @endforeach
    @endif
</div>
