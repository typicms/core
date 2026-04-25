@props([
    'date',
    'format' => 'LL',
])

<time datetime="{{ $date->format('Y-m-d') }}">{{ $date->isoFormat($format) }}</time>
