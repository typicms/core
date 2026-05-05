@use(TypiCMS\Modules\Core\Models\Page)

@if (!$model->id)
    @push('js')
        <script type="module">
            new TomSelect('#parent_id', {
                maxOptions: undefined,
            });
        </script>
    @endpush
@endif

<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Pages')" :default-title="__('New page')" />

<div class="form-body">
    <x-core::form-errors />

    <div class="row">
        <div class="col-lg-8">
            <div class="row gx-3">
                <div class="col-md-6">
                    <x-transbootform::text :label="__('Title')" name="title" />
                </div>
                <div class="col-md-6">
                    @foreach (locales() as $locale)
                        <div class="mb-3 form-group-translation">
                            <label class="form-label" for="slug[{{ $locale }}]">
                                <span>{{ __('Url') }}</span>
                                ({{ $locale }})
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $model->parentUri($locale) }}</span>
                                <input
                                    class="form-control @if ($errors->has('slug.' . $locale)) is-invalid @endif"
                                    type="text"
                                    name="slug[{{ $locale }}]"
                                    id="slug[{{ $locale }}]"
                                    value="{{ $model->translate('slug', $locale) }}"
                                    data-slug="title[{{ $locale }}]"
                                    data-language="{{ $locale }}"
                                />
                                <button class="btn btn-outline-secondary btn-slug" type="button">{{ __('Generate') }}</button>
                                {!! $errors->first('slug.' . $locale, '<div class="invalid-feedback">:message</div>') !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <x-transbootform::hidden name="uri" />

            <div class="mb-3">
                <x-transbootform::checkbox :label="__('Published')" name="status" :unchecked-value="0" />
            </div>

            @if (!$model->id)
                <x-bootform::select :label="__('Subpage of')" name="parent_id" :options="new Page()->allForSelect(withPagesLinkedToAModule: false)" />
            @endif

            <x-core::tiptap-editors :$model name="body" :label="__('Body')" />

            @can('read page_sections')
                @if ($model->id)
                    <item-list
                        url-base="/api/pages/{{ $model->id }}/sections"
                        fields="id,image_id,page_id,position,status,title,template"
                        table="page_sections"
                        title="sections"
                        include="image"
                        :sub-list="true"
                        :searchable="['title']"
                        :sorting="['position']"
                        :draggable="$can('update page_sections')"
                    >
                        <template #top-buttons v-if="$can('create page_sections')">
                            <x-core::create-button :url="route('admin::create-page_section', $model->id)" :label="__('Create page section')" />
                        </template>

                        <template #columns="{ sortArray }">
                            <item-list-column-header name="position" sortable :sort-array="sortArray" :label="$t('Order')"></item-list-column-header>
                            <item-list-column-header name="checkbox" v-if="$can('update page_sections')||$can('delete page_sections')"></item-list-column-header>
                            <item-list-column-header name="edit" v-if="$can('update page_sections')"></item-list-column-header>
                            <item-list-column-header name="status_translated" sortable :sort-array="sortArray" :label="$t('Status')"></item-list-column-header>
                            <item-list-column-header name="image" :label="$t('Image')"></item-list-column-header>
                            <item-list-column-header name="title_translated" sortable :sort-array="sortArray" :label="$t('Title')"></item-list-column-header>
                            <item-list-column-header name="template" :label="$t('Template')"></item-list-column-header>
                        </template>

                        <template #table-row="{ model, checkedModels, loading, sortArray }">
                            <td class="drag-handle text-muted" v-if="$can('update partners')" :style="{ cursor: sortArray[0] === 'position' ? 'grab' : 'default' }">
                                <i :class="['icon-grip-vertical', { 'opacity-50': sortArray[0] !== 'position' }]"></i>
                            </td>
                            <td class="checkbox" v-if="$can('update page_sections')||$can('delete page_sections')">
                                <item-list-checkbox :model="model" :checked-models-prop="checkedModels" :loading="loading"></item-list-checkbox>
                            </td>
                            <td v-if="$can('update page_sections')">
                                <item-list-edit-button :url="'/admin/pages/' + model.page_id + '/sections/' + model.id + '/edit'"></item-list-edit-button>
                            </td>
                            <td>
                                <item-list-status-button :model="model"></item-list-status-button>
                            </td>
                            <td><img v-if="model.image" :src="model.thumb" alt="" height="27" /></td>
                            <td>@{{ model.title_translated }}</td>
                            <td>
                                <span class="badge text-bg-warning">@{{ model.template.replace(new RegExp('-', 'g'), ' ') }}</span>
                            </td>
                        </template>
                    </item-list>
                @else
                    <p class="alert alert-info">{{ __('Save this page first, then add sections.') }}</p>
                @endif
            @endcan
        </div>

        <div class="col-lg-4">
            <div class="right-column">
                @if (!$model->redirect)
                    <file-manager></file-manager>
                    <file-field type="image" field="image_id" :init-file="{{ $model->image ?? 'null' }}"></file-field>
                    <file-field type="image" field="og_image_id" :init-file="{{ $model->ogImage ?? 'null' }}" label="{{ __('Social Share Image') }}" hint="1200 × 630 px"></file-field>
                    <files-field :init-files="{{ $model->files }}"></files-field>
                    <x-transbootform::textarea :label="__('Meta title')" name="meta_title" rows="2" />
                    <x-transbootform::textarea :label="__('Meta description')" name="meta_description" rows="4" />
                    <x-transbootform::text :label="__('Meta keywords')" name="meta_keywords" />
                @endif

                <div class="mb-3">
                    @if (!$model->redirect)
                        <x-bootform::checkbox :label="__('Is home')" name="is_home" :unchecked-value="0" />
                        <x-bootform::checkbox :label="__('Private')" name="private" :unchecked-value="0" />
                    @endif
                    <x-bootform::checkbox :label="__('Redirect to first child')" name="redirect" :unchecked-value="0" />
                </div>
                @if (!$model->redirect)
                    @if ($model->subpages->count() > 0)
                        <x-bootform::select :label="__('Module')" name="module" :options="getModulesForSelect()" disabled :help="__('A page containing subpages cannot be linked to a module')" />
                    @else
                        <x-bootform::select :label="__('Module')" name="module" :options="getModulesForSelect()" />
                    @endif
                    <x-bootform::select :label="__('Template')" name="template" :options="pageTemplates()" />
                    @if (!$model->id)
                        <x-bootform::select :label="__('Add to menu')" name="add_to_menu" :options="['' => ''] + TypiCMS\Modules\Core\Models\Menu::all()->pluck('name', 'id')->all()" />
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
