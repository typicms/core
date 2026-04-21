@props(['backUrl', 'backLabel' => __('Back')])

<a class="btn-back" href="{{ $backUrl }}" title="{{ __('Back') }}">
    <i class="icon-arrow-left"></i>
    <span class="btn-back-label">{{ $backLabel }}</span>
</a>
