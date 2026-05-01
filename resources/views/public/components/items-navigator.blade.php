@props(['model', 'page'])

<div class="items-navigator">
    <a class="items-navigator-back" href="{{ $page->url() }}">← {{ __("Back to {$model->getTable()} list") }}</a>
    <div class="items-navigator-previous-next">
        @php
            $prevUrl = $model->prev()?->url();
            $nextUrl = $model->next()?->url();
        @endphp
        <a @class(['items-navigator-previous', 'disabled' => !$prevUrl]) href="{{ $prevUrl }}">← {{ __('Previous') }}</a>
        <a @class(['items-navigator-next', 'disabled' => !$nextUrl]) href="{{ $nextUrl }}">{{ __('Next') }} →</a>
    </div>
</div>
