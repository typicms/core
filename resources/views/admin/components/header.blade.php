@props(['model', 'backUrl' => null, 'backLabel' => null, 'defaultTitle' => null, 'preview' => true, 'langSwitcher' => true])

<div class="form-header">
    <div class="form-header-top">
        <div>
            <x-core::back-button :$backUrl :$backLabel />
            <x-core::title :$model :$defaultTitle />
        </div>
    </div>
    <x-core::form-buttons :$model :$preview :$langSwitcher />
</div>
