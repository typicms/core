<template>
    <div class="btn-group btn-group-sm">
        <button id="dropdownActions" :disabled="numberOfCheckedModels === 0 || loading" aria-expanded="true" aria-haspopup="true" class="btn-solid theme-secondary" data-bs-toggle="menu" type="button">
            {{ $t('Action') }}
        </button>
        <div aria-labelledby="dropdownActions" class="menu">
            <button v-if="publishable" class="menu-item" type="button" @click="$emit('publish')">
                {{ $t('Publish') }}
                <span class="fg-secondary">({{ locale }})</span>
            </button>
            <button v-if="publishable" class="menu-item" type="button" @click="$emit('unpublish')">
                {{ $t('Unpublish') }}
                <span class="fg-secondary">({{ locale }})</span>
            </button>
            <div v-if="publishable" class="menu-divider"></div>
            <button v-if="duplicable" class="menu-item" type="button" @click="$emit('duplicate')">
                {{ $t('Duplicate') }}
            </button>
            <div v-if="duplicable" class="menu-divider"></div>
            <button v-if="deletable" class="menu-item" type="button" @click="$emit('destroy')">
                {{ $t('Delete') }}
            </button>
            <div class="divider" role="separator"></div>
            <button class="menu-item" disabled type="button">
                <small>{{
                    $t('# items selected', numberOfCheckedModels, {
                        count: numberOfCheckedModels,
                    })
                }}</small>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    publishable: {
        type: Boolean,
        default: true,
    },
    deletable: {
        type: Boolean,
        default: true,
    },
    duplicable: {
        type: Boolean,
        default: false,
    },
    numberOfCheckedModels: {
        type: Number,
        required: true,
    },
    loading: {
        type: Boolean,
        required: true,
    },
});

const locale = ref(TypiCMS.content_locale);
</script>
