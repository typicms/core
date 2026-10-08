@use('TypiCMS\Modules\Core\Support\ModuleUrl')
@php($searchUrl = ModuleUrl::index('search'))
@if ($searchUrl)
    <dialog class="search-modal dialog dialog-xl" id="search-modal" data-bs-backdrop="static">
        <div class="container">
            <div class="search-modal-content">
                <form class="search-form" method="get" action="{{ $searchUrl }}">
                    <div class="input-group input-group-lg">
                        <input
                            class="search-form-input form-control"
                            type="text"
                            name="query"
                            id="search-modal-input"
                            aria-label="@lang('Type here to search')"
                            placeholder="@lang('Type here to search')"
                            value="{{ request('query') }}"
                        />
                        <button class="search-form-button btn-solid theme-primary" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="dialog" aria-label="Close"></button>
    </dialog>
    <script>
        const myModal = document.getElementById('search-modal');
        const myInput = document.getElementById('search-modal-input');

        myModal.addEventListener('shown.bs.dialog', () => {
            myInput.focus();
        });
    </script>
@endif
