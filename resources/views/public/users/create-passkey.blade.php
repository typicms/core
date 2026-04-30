@push('js')
    <script type="module">
        document.getElementById('create-passkey-button').addEventListener('click', async function() {
            const apiTokenElement = document.head.querySelector('meta[name="api-token"]');
            const csrfTokenElement = document.head.querySelector('meta[name="csrf-token"]');
            const responseOptions = await fetch('/api/passkeys/generate-options', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    Authorization: `Bearer ${apiTokenElement.content}`,
                    'X-CSRF-TOKEN': csrfTokenElement.content,
                }
            });
            const options = await responseOptions.json();
            const startAuthenticationResponse = await window.startRegistration(options);

            const response = await fetch('/api/passkeys', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    Authorization: `Bearer ${apiTokenElement.content}`,
                    'X-CSRF-TOKEN': csrfTokenElement.content,
                },
                body: JSON.stringify({
                    name: '{{ auth()->user()->first_name . '’s passkey' }}',
                    options: JSON.stringify(options),
                    passkey: JSON.stringify(startAuthenticationResponse),
                }),
            });

            if (response.ok) {
                window.location.href = '/admin';
            }
        });
    </script>
@endpush

<x-core::layouts.auth :title="__('Login')">
    <div id="login" class="container-login auth auth-sm">
        <x-core::auth-header />
        <div class="auth-form">
            <h1 class="auth-title">{{ __('Create a passkey') }}</h1>
            <p class="alert alert-info">@lang('Please create a passkey for future connections.')</p>
            <x-core::status />
            <div class="mb-3 d-grid">
                <button class="btn btn-lg btn-primary" type="button" id="create-passkey-button">
                    <i class="icon-key-round"></i>
                    @lang('Create passkey')
                </button>
                <a class="text-body text-decoration-underline small text-center mt-3 d-block" href="{{ route('admin::dashboard') }}">@lang('I will do it later.')</a>
            </div>
        </div>
        <x-core::back-to-website-link />
    </div>
</x-core::layouts.auth>
