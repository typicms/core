@if ($message = session('status'))
    <div class="alert theme-info" role="alert">{{ $message }}</div>
@endif
