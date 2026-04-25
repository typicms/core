@props(['model', 'page'])

<div class="items-navigator">
    <a class="items-navigator-back" href="{{ $page->url() }}">
        ←
        @lang("Back to {$model->getTable()} list")
    </a>
    <div class="items-navigator-previous-next">
        @php
            $prevUrl = (new ($model::class))->prev($model)?->url();
            $nextUrl = (new ($model::class))->next($model)?->url();
        @endphp
        <a @class(['items-navigator-previous', 'disabled' => !$prevUrl]) href="{{ $prevUrl }}">
            ←
            @lang('Previous')
        </a>
        <a @class(['items-navigator-next', 'disabled' => !$nextUrl]) href="{{ $nextUrl }}">
            @lang('Next')
            →
        </a>
    </div>
</div>
