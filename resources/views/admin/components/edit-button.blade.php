@props(['url' => ''])

@can('can:update page_sections')
    <div class="edit">
        <a class="edit-button" href="{{ $url }}" title="{{ __('Edit') }}">
            <span class="icon-pencil"></span>
            <span class="visually-hidden">{{ __('Edit') }}</span>
        </a>
    </div>
@endcan
