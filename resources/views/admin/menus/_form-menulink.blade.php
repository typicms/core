@push('js')
    <script type="module">
        new TomSelect('#page_id', {
            maxOptions: undefined,
        });
    </script>
@endpush

<x-core::header :$model :back-url="$menu->editUrl()" :back-label="$menu->name" :default-title="__('New menulink')" />

<div class="form-body">
    <x-core::form-errors />
    <div class="row">
        <div class="col-lg-8">
            <x-bootform::hidden name="id" />
            <x-bootform::hidden name="menu_id" :value="$menu->id" />
            <x-bootform::hidden name="position" />
            <x-bootform::hidden name="parent_id" />
            <x-transbootform::text :label="__('Title')" name="title" />
            <div class="mb-3">
                <x-transbootform::checkbox :label="__('Published')" name="status" :unchecked-value="0" />
            </div>
            <x-transbootform::textarea :label="__('Description')" name="description" rows="3" />
            <x-bootform::select :label="__('Page')" name="page_id" :options="new TypiCMS\Modules\Core\Models\Page()->allForSelect()" />
            <x-bootform::select :label="__('Section')" name="section_id" :options="['' => '']" />
            <x-transbootform::text :label="__('Website')" name="website" type="url" placeholder="https://" />
            <x-bootform::select :label="__('Target')" name="target" :options="['' => __('Active tab'), '_blank' => __('New tab')]" />
        </div>
        <div class="col-lg-4">
            <div class="right-column">
                <file-manager></file-manager>
                <file-field type="image" field="image_id" :init-file="{{ $model->image ?? 'null' }}"></file-field>
                <x-bootform::text :label="__('Class')" name="class" />
            </div>
        </div>
    </div>
</div>
@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectPage = document.getElementById('page_id');
            const selectSection = document.getElementById('section_id');
            const selectedSectionId = parseInt('{{ $model->section_id }}');

            function initSelect() {
                for (let i = selectSection.length - 1; i >= 0; i--) {
                    if (selectSection.options[i].value !== '') {
                        selectSection.remove(i);
                    }
                }
            }

            function getSections() {
                initSelect();
                const pageId = selectPage.options[selectPage.selectedIndex].value;
                if (!pageId) {
                    return;
                }

                // Get sections and create <option> elements.
                fetch('/api/pages/' + pageId + '/sections?sort=position&fields[page_sections]=id,position,title', {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        Authorization: `Bearer ${document.head.querySelector('meta[name="api-token"]').content}`,
                        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        const sections = data.data;
                        for (let i = 0; i < sections.length; i++) {
                            let option = document.createElement('option');
                            option.value = sections[i].id;
                            option.innerHTML = sections[i].title_translated + ' (#' + sections[i].id + ')';
                            if (sections[i].id === selectedSectionId) {
                                option.selected = true;
                            }
                            selectSection.appendChild(option);
                        }
                    });
            }

            getSections();
            selectPage.onchange = getSections;
        });
    </script>
@endpush
