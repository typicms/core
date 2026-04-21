<x-core::header :$model :backUrl="$model->indexUrl()" :backLabel="__('Tags')" :defaultTitle="__('New tag')" :lang-switcher="false" />

<div class="form-body">
    <x-core::form-errors />

    <div class="row gx-3">
        <div class="col-md-6">
            {!! BootForm::text(__('Tag'), 'tag')->required() !!}
        </div>
        <div class="col-md-6 mb-3 @if ($errors->has('slug')) has-error @endif">
            {!! Form::label(__('Slug'))->addClass('form-label')->forId('slug') !!}
            <div class="input-group">
                {!! Form::text('slug')->addClass('form-control')->addClass($errors->has('slug') ? 'is-invalid' : '')->id('slug')->data('slug', 'tag') !!}
                <button class="btn btn-outline-secondary btn-slug @if ($errors->has('slug')) btn-danger @endif" type="button">
                    {{ __('Generate') }}
                </button>
                {!! $errors->first('slug', '<div class="invalid-feedback">:message</div>') !!}
            </div>
        </div>
    </div>

    @if ($model->id)
        @php
            $taggedItems = $model->getTaggedItemsGrouped();
            $totalCount = array_sum(array_map(fn($items) => $items->count(), $taggedItems));
        @endphp

        @if ($totalCount > 0)
            <div class="mt-3">
                <h2 class="mb-3">{{ __('Tagged items') }} ({{ $totalCount }})</h2>
                @foreach ($taggedItems as $type => $items)
                    <div class="mb-4">
                        <h3>{{ $items->count() }} @choice($type, $items->count()) </h3>
                        <ul class="list-group list-group-flush">
                            @foreach ($items as $item)
                                <li class="list-group-item d-flex gap-2 align-items-center px-0">
                                    @if (method_exists($item, 'editUrl'))
                                        <a class="btn btn-xs btn-light" href="{{ $item->editUrl() }}">
                                            {{ __('Edit') }}
                                        </a>
                                    @endif
                                    <div>
                                        @if (method_exists($item, 'presentTitle'))
                                            {{ $item->presentTitle() }}
                                        @elseif(isset($item->title))
                                            {{ $item->title }}
                                        @elseif(isset($item->name))
                                            {{ $item->name }}
                                        @else
                                            {{ $type }} #{{ $item->id }}
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mt-4">
                {{ __('No items are tagged with this tag yet.') }}
            </div>
        @endif
    @endif
</div>
