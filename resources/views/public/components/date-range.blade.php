@if ($start->isSameDay($end))
    <time datetime="{{ $start->format('Y-m-d') }}">{{ $start->isoFormat($endFormat) }}</time>
@else
    <time datetime="{{ $start->format('Y-m-d') }}">{{ $start->isoFormat($startFormat) }}</time>{{ $separator }}<time datetime="{{ $end->format('Y-m-d') }}">{{ $end->isoFormat($endFormat) }}</time>
@endif
