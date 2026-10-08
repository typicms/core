@props(['model', 'defaultTitle' => __('New')])

@if (!$model->id)
    <h1 class="header-title">{{ $defaultTitle }}</h1>
@else
    <h1 class="header-title @if (!$model->presentTitle()) fg-secondary @endif">{{ $model->presentTitle() ?: __('Untitled') }}</h1>
@endif
