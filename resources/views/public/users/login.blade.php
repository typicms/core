<x-core::layouts.auth :title="__('Login')">
    <div id="login" class="container-login auth auth-sm">
        <x-core::auth-header />
        <x-core::authenticate-passkey />
        <x-core::status />
        <x-core::register-info />
        <x-core::back-to-website-link />
    </div>
</x-core::layouts.auth>
