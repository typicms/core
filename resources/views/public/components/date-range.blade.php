@if ($start->isSameDay($end))
    <time datetime="{{ $start->format('Y-m-d') }}">{{ $start->isoFormat($format . ' YYYY') }}</time>
@else
    <time datetime="{{ $start->format('Y-m-d') }}">{{ $start->isoFormat($startFormat) }}</time>{{ $spacer }}<time datetime="{{ $end->format('Y-m-d') }}">{{ $end->isoFormat($endFormat) }}</time>
@endif
