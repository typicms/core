@props(['model', 'page'])

<div class="items-navigator">
    <a class="btn btn-sm btn-solid theme-secondary items-navigator-back" href="{{ $page->url() }}">← {{ __("Back to {$model->getTable()} list") }}</a>
    <div class="btn-group btn-group-sm items-navigator-previous-next">
        @php
            $prevUrl = $model->prev()?->url();
            $nextUrl = $model->next()?->url();
        @endphp
        <a @class(['btn btn-solid theme-secondary items-navigator-previous', 'disabled' => ! $prevUrl]) href="{{ $prevUrl }}"
            >← {{ __('Previous') }}</a>
        <a @class(['btn btn-solid theme-secondary items-navigator-next', 'disabled' => ! $nextUrl]) href="{{ $nextUrl }}">{{ __('Next') }} →</a>
    </div>
</div>
