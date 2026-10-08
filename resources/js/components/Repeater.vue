<template>
    <div class="mb-5">
        <input v-if="items.length === 0" :name="name" type="hidden" />
        <label class="form-label">{{ t(title) }}</label>

        <draggable v-if="items.length > 0" v-model="items" :group="'items_' + name" class="d-flex flex-column mb-5 gap-5" handle=".handle" item-key="id" @change="errors = []">
            <template #item="{ element, index }">
                <div class="d-flex card item border">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <grip-vertical class="handle fg-secondary" size="16" />
                        <button class="btn-solid theme-danger btn-xs" @click.prevent="remove(element)">{{ t('Delete') }}</button>
                    </div>
                    <div class="card-body d-flex justify-content-between flex-row flex-wrap gap-3">
                        <div v-for="field in fields" :key="field.name" :class="[{ 'flex-grow-1': field.type !== 'hidden' }, field.class]">
                            <template v-if="field.translatable">
                                <repeater-field
                                    v-for="locale in locales"
                                    :key="'item_' + element.id + '_' + field.name + '_' + locale.short"
                                    v-model="element[field.name][locale.short]"
                                    :errors="getError(index, field.name, locale.short)"
                                    :field="field"
                                    :field-name="name"
                                    :index="index"
                                    :locale="locale.short"
                                ></repeater-field>
                            </template>
                            <repeater-field
                                v-else
                                :key="'item_' + element.id + '_' + field.name"
                                v-model="element[field.name]"
                                :errors="getError(index, field.name, null)"
                                :field="field"
                                :field-name="name"
                                :index="index"
                            ></repeater-field>
                        </div>
                    </div>
                </div>
            </template>
        </draggable>
        <div>
            <button :disabled="maxItems !== null && items.length >= maxItems" class="btn-solid theme-secondary btn-sm" @click.prevent="add">
                <circle-plus-icon size="16" />
                {{ t('Add') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { CirclePlusIcon, GripVertical } from '@lucide/vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';

import RepeaterField from './RepeaterField.vue';

const { t } = useI18n();

const props = defineProps({
    initItems: {
        type: Array,
    },
    config: {
        type: Object,
        required: true,
    },
    initErrors: {
        type: Array,
        required: true,
    },
});

const locales = ref(window.TypiCMS.locales);
const items = ref((props.initItems || []).map((item) => ({ ...item, id: item.id || crypto.randomUUID() })));
const title = ref(props.config.title);
const name = ref(props.config.name);
const maxItems = ref(props.config.max_items || null);
const fields = ref(props.config.fields);
const errors = ref(props.initErrors);

function add() {
    if (maxItems.value === null || items.value.length < maxItems.value) {
        items.value.push(emptyItem());
    }
}

function emptyItem() {
    const item = { id: crypto.randomUUID() };
    fields.value.forEach((field) => {
        if (field.translatable) {
            item[field.name] = {};
            locales.value.forEach((locale) => {
                item[field.name][locale.short] = field.default !== undefined ? field.default : null;
            });
        } else {
            item[field.name] = field.default !== undefined ? field.default : null;
        }
    });

    return item;
}

function getError(index, fieldName, locale) {
    if (errors.value.length === 0) {
        return [];
    }
    if (locale !== null) {
        if (errors.value[index] === undefined) {
            return [];
        }
        if (errors.value[index][fieldName] === undefined) {
            return [];
        }
        return errors.value[index][fieldName][locale] ?? [];
    }
    if (errors.value[index] === undefined) {
        return [];
    }
    return errors.value[index][fieldName] ?? [];
}

function remove(item) {
    const index = items.value.indexOf(item);
    items.value.splice(index, 1);
}
</script>
