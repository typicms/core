@props(['url', 'label'])

<a class="btn-solid theme-primary btn-sm header-btn-add" href="{{ $url }}">
    <i class="icon-circle-plus fg-white"></i>
    {{ $label }}
</a>
