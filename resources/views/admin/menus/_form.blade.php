<x-core::header :$model :back-url="$model->indexUrl()" :back-label="__('Menus')" :default-title="__('New menu')" />

<div class="form-body">
    <x-core::form-errors />

    <div class="row">
        <div class="col-lg-8">
            @if ($model->id)
                <x-bootform::hidden name="name" />
            @else
                <x-bootform::text :label="__('Name')" name="name" required autocomplete="off" />
            @endif

            <div class="mb-3">
                <x-transbootform::checkbox :label="__('Published')" name="status" :unchecked-value="0" />
            </div>

            @if ($model->id)
                <item-list-tree
                    url-base="/api/menus/{{ $model->id }}/menulinks"
                    fields="id,menu_id,page_id,position,parent_id,status,title,website"
                    table="menulinks"
                    title="menulinks"
                    v-if="$can('read menulinks')"
                    :sub-list="true"
                >
                    <template #top-buttons v-if="$can('create menulinks')">
                        <x-core::create-button :url="route('admin::create-menulink', $model->id)" :label="__('Create menulink')" />
                    </template>
                </item-list-tree>
            @endif
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
