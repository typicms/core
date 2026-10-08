<div class="sidebar">
    <dialog class="sidebar-drawer lg:drawer drawer-start" id="offcanvasResponsive" aria-labelledby="offcanvasResponsiveLabel">
        <div class="sidebar-header drawer-header">
            <button
                class="btn-close"
                type="button"
                data-bs-dismiss="drawer"
                data-bs-target="#offcanvasResponsive"
                aria-controls="offcanvasResponsive"
                aria-label="{{ __('Close navigation') }}"
            ></button>
        </div>
        {!! $sidebar->render() !!}
    </dialog>
</div>
