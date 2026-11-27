<div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" data-close-label="{{ __('Close') }}"></div>

<script type="module">
    @if (session('message'))
        toast.success(@js(session('message')));
    @endif
    @if (session('success'))
        toast.success(@js(session('success')));
    @endif
    @if (session('error'))
        toast.error(@js(session('error')));
    @endif
</script>
