@if (Route::has(app()->getLocale() . '::search'))
    <div class="modal fade modal-xl search-modal" id="search-modal" data-bs-backdrop="static" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="container">
                <div class="modal-content search-modal-content">
                    <form class="search-form" method="get" action="{{ route(app()->getLocale() . '::search') }}">
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
                            <button class="search-form-button btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <script>
        const myModal = document.getElementById('search-modal');
        const myInput = document.getElementById('search-modal-input');

        myModal.addEventListener('shown.bs.modal', () => {
            myInput.focus();
        });
    </script>
@endif
