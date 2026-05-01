<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Translations')" :default-title="__('New translation')" :lang-switcher="false" />

<div class="form-body">
    <x-core::form-errors />

    @if (!$model->id)
        {!! BootForm::text(__('Key'), 'key')->required() !!}
    @endif

    <p class="form-label">{{ __('Translations') }}</p>

    @foreach (locales() as $locale)
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text">{{ strtoupper($locale) }}</span>
                {!!
                    Form::text('translation[' . $locale . ']')
                        ->addClass('form-control')
                        ->addClass($errors->has('translation.' . $locale) ? 'is-invalid' : '')
                !!} {!! $errors->first('translation.' . $locale, '<div class="invalid-feedback">:message</div>') !!}
            </div>
        </div>
    @endforeach
</div>
