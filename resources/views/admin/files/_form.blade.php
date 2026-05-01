<x-core::header :$model :back-url="$model->indexUrl()" :back-label="$model->folder?->name ?? __('Files')" :default-title="__('New file')" :preview="false" />

<div class="form-body">
    <x-core::form-errors />

    {!! BootForm::hidden('id') !!} {!! BootForm::hidden('type') !!} {!! BootForm::hidden('position')->value($model->position ?: 0) !!} {!! BootForm::hidden('path') !!} {!! BootForm::hidden('extension') !!} {!! BootForm::hidden('mimetype') !!} {!! BootForm::hidden('width') !!} {!! BootForm::hidden('height') !!}

    @if ($model->type === 'f')
        {!! BootForm::text(__('Name'), 'name')->autocomplete('off') !!}
    @else
        <div class="row gx-3">
            <div class="col-lg-6">
                {!! TranslatableBootForm::text(__('Title'), 'title') !!} {!! TranslatableBootForm::textarea(__('Description'), 'description') !!}

                @if ($model->type === 'i')
                    {!! TranslatableBootForm::text(__('Alt attribute'), 'alt_attribute') !!}
                @endif

                {!! BootForm::text(__('Display name'), 'name')->autocomplete('off') !!}

                {!! BootForm::file(__('Replace file'), 'file') !!}
            </div>

            <div class="col-lg-6">
                @if ($model->type === 'i')
                    @if ($model->extension !== 'svg')
                        <image-cropper :image-url="'{{ Storage::url($model->path) }}'" :file-id="{{ $model->id }}"></image-cropper>
                    @else
                        <img class="img-fluid mb-3" src="{{ Storage::url($model->path) }}" alt="{{ $model->alt_attribute }}" />
                    @endif
                @endif

                <table class="table">
                    <tbody>
                        <tr>
                            <th class="w-25">{{ __('URL') }}</th>
                            <td>
                                <div class="d-flex align-items-start justify-content-between">
                                    <a href="{{ Storage::url($model->path) }}" target="_blank" rel="noopener noreferrer">{{ Storage::url($model->path) }}</a>
                                    <button class="btn btn-light btn-xs text-nowrap" type="button" onclick="copyToClipboard('{{ Storage::url($model->path) }}')">{{ __('Copy') }}</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Path') }}</th>
                            <td>{{ $model->path }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Display name') }}</th>
                            <td>{{ $model->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Extension') }}</th>
                            <td>{{ $model->extension }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Mimetype') }}</th>
                            <td>{{ $model->mimetype }}</td>
                        </tr>
                        @if ($model->width)
                            <tr>
                                <th>{{ __('Width') }}</th>
                                <td>{{ $model->width }} px</td>
                            </tr>
                        @endif
                        @if ($model->height)
                            <tr>
                                <th>{{ __('Height') }}</th>
                                <td>{{ $model->height }} px</td>
                            </tr>
                        @endif
                        <tr>
                            <th>{{ __('Created at') }}</th>
                            <td>{{ $model->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Updated at') }}</th>
                            <td>{{ $model->updated_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @push('js')
        <script>
            function copyToClipboard(content) {
                let textArea = document.createElement('textarea');
                textArea.value = content;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('Copy');
                textArea.remove();
                alertify.success('{{ __('Copied to the clipboard') }}');
            }
        </script>
    @endpush
</div>
