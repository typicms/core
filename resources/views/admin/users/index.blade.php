<x-core::layouts.admin :title="__('Users')">
    <item-list
        url-base="/api/users"
        fields="id,first_name,last_name,email,activated,superuser,roles.name"
        table="users"
        title="users"
        include="roles,rolesCount"
        :translatable="false"
        :publishable="false"
        :exportable="true"
        :searchable="['first_name,last_name,email']"
        :sorting="['first_name']"
    >
        <template #top-buttons v-if="$can('create users')">
            <x-core::create-button :url="route('admin::create-user')" :label="__('Create user')" />
        </template>

        <template #columns="{ sortArray }">
            <item-list-column-header name="checkbox" v-if="$can('update users')||$can('delete users')"></item-list-column-header>
            <item-list-column-header name="edit" v-if="$can('update users')"></item-list-column-header>
            <item-list-column-header name="impersonate" v-if="$can('impersonate users')"></item-list-column-header>
            <item-list-column-header name="first_name" sortable :sort-array="sortArray" :label="$t('First name')"></item-list-column-header>
            <item-list-column-header name="last_name" sortable :sort-array="sortArray" :label="$t('Last name')"></item-list-column-header>
            <item-list-column-header name="email" sortable :sort-array="sortArray" :label="$t('Email')"></item-list-column-header>
            <item-list-column-header name="activated" sortable :sort-array="sortArray" :label="$t('Activated')"></item-list-column-header>
            <item-list-column-header name="roles_count" sortable :sort-array="sortArray" :label="$t('Roles')"></item-list-column-header>
        </template>

        <template #table-row="{ model, checkedModels, loading }">
            <td class="checkbox" v-if="$can('update users')||$can('delete users')">
                <item-list-checkbox :model="model" :checked-models-prop="checkedModels" :loading="loading"></item-list-checkbox>
            </td>
            <td v-if="$can('update users')">
                <item-list-edit-button :url="'/admin/users/' + model.id + '/edit'"></item-list-edit-button>
            </td>
            <td v-if="$can('impersonate users')">
                <a
                    class="btn-impersonate btn btn-link btn-sm text-secondary"
                    title="Impersonate"
                    onclick="if(!confirm('{{ __('Impersonate this user?') }}'))return false"
                    :href="'/admin/users/' + model.id + '/impersonate'"
                ></a>
            </td>
            <td>@{{ model.first_name }}</td>
            <td>@{{ model.last_name }}</td>
            <td>
                <a :href="'mailto:' + model.email">@{{ model.email }}</a>
            </td>
            <td>
                <span class="badge bg-warning" v-if="model.activated">{{ __('Yes') }}</span>
                <span class="badge text-bg-secondary" v-else>{{ __('No') }}</span>
            </td>
            <td>
                @if (auth()->user()->isSuperUser())
                    <span class="badge text-bg-warning me-1" v-if="model.superuser">Superuser</span>
                @endif

                <span class="badge text-bg-info me-1" v-for="role in model.roles">@{{ role.name }}</span>
            </td>
        </template>
    </item-list>
</x-core::layouts.admin>
