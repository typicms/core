<x-core::layouts.admin :title="__('Pages')">
    <item-list-tree url-base="/api/pages" fields="id,position,parent_id,module,redirect,is_home,private,status,title,slug,uri" table="pages" :searchable="['title']" title="pages">
        <template #top-buttons v-if="$can('create pages')">
            <x-core::create-button :url="route('admin::create-page')" :label="__('Create page')" />
        </template>
    </item-list-tree>
</x-core::layouts.admin>
