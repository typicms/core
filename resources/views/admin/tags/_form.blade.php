<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Tags')" :default-title="__('New tag')" :lang-switcher="false" />

<div class="form-body">
    <x-core::form-errors />

    <div class="row gx-5">
        <div class="md:col-6">
            <x-bootform::text :label="__('Tag')" name="tag" required />
        </div>
        <div class="md:col-6">
            <x-bootform::input-group :label="__('Slug')" name="slug" data-slug="tag">
                <x-slot:after-addon>
                    <x-bootform::button :value="__('Generate')" type="btn-outline theme-secondary" :class="'btn-slug'" />
                </x-slot:after-addon>
            </x-bootform::input-group>
        </div>
    </div>

    @if ($model->id)
        @php
            $taggedItems = $model->getTaggedItemsGrouped();
            $totalCount = array_sum(array_map(fn($items) => $items->count(), $taggedItems));
        @endphp
        @if ($totalCount > 0)
            <div class="mt-5">
                <h2 class="mb-5">{{ __('Tagged items') }} ({{ $totalCount }})</h2>
                @foreach ($taggedItems as $type => $items)
                    <div class="mb-7">
                        <h3>
                            {{ $items->count() }}
                            @choice($type, $items->count())
                        </h3>
                        <ul class="list-group list-group-flush">
                            @foreach ($items as $item)
                                <li class="list-group-item d-flex gap-3 align-items-center px-0">
                                    @if (method_exists($item, 'editUrl'))
                                        <a class="btn-xs btn-solid theme-secondary" href="{{ $item->editUrl() }}">{{ __('Edit') }}</a>
                                    @endif
                                    <div>
                                        @if (method_exists($item, 'presentTitle'))
                                            {{ $item->presentTitle() }}
                                        @elseif (isset($item->title))
                                            {{ $item->title }}
                                        @elseif (isset($item->name))
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
            <div class="alert theme-info mt-7">{{ __('No items are tagged with this tag yet.') }}</div>
        @endif
    @endif
</div>
