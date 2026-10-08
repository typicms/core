<template>
    <div>
        <input v-if="type === 'hidden'" :id="fieldId" :name="fieldNameComplete" :type="type" :value="modelValue" />
        <div
            v-if="['text', 'url', 'tel', 'email', 'date', 'month', 'week', 'time', 'datetime-local', 'number', 'range', 'color'].includes(type)"
            :class="{ 'form-group-translation': locale !== null }"
        >
            <label :for="fieldId" class="form-label">{{ fieldLabel }}</label>
            <input
                :id="fieldId"
                :class="{ 'is-invalid': errors.length > 0 }"
                :data-language="locale"
                :max="max"
                :min="min"
                :name="fieldNameComplete"
                :placeholder="placeholder"
                :required="required"
                :step="step"
                :type="type"
                :value="modelValue"
                :readonly="readonly"
                :disabled="disabled"
                class="form-control"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <div v-if="errors.length > 0" class="invalid-feedback">{{ errors[0] }}</div>
        </div>
        <div v-if="type === 'textarea'" :class="{ 'form-group-translation': locale !== null }">
            <label :for="fieldId" class="form-label">{{ fieldLabel }}</label>
            <textarea
                :id="fieldId"
                :class="{ 'is-invalid': errors.length > 0 }"
                :data-language="locale"
                :name="fieldNameComplete"
                :placeholder="placeholder"
                :required="required"
                :rows="rows"
                :value="modelValue"
                :readonly="readonly"
                :disabled="disabled"
                class="form-control"
                @input="$emit('update:modelValue', $event.target.value)"
            ></textarea>
            <div v-if="errors.length > 0" class="invalid-feedback">{{ errors[0] }}</div>
        </div>
        <div v-if="type === 'select'" :class="{ 'form-group-translation': locale !== null }">
            <label :for="fieldId" class="form-label">{{ fieldLabel }}</label>
            <select
                :id="fieldId"
                :class="{ 'is-invalid': errors.length > 0 }"
                :data-language="locale"
                :name="fieldNameComplete"
                :required="required"
                :value="modelValue"
                :disabled="disabled"
                class="form-control"
                @change="$emit('update:modelValue', $event.target.value)"
            >
                <option v-for="(label, value) in items" :key="value" :value="value">{{ t(label) }}</option>
            </select>
            <div v-if="errors.length > 0" class="invalid-feedback">{{ errors[0] }}</div>
        </div>
        <div v-if="type === 'checkbox'" :class="{ 'form-group-translation': locale !== null }">
            <p class="form-label">{{ fieldLabel }}</p>
            <div class="form-field">
                <input :name="fieldNameComplete" type="hidden" value="0" />
                <input
                    :id="fieldId"
                    :checked="+modelValue === 1"
                    :class="{ 'is-invalid': errors.length > 0 }"
                    :data-language="locale"
                    :name="fieldNameComplete"
                    :required="required"
                    :type="type"
                    :value="1"
                    :disabled="disabled"
                    class="check"
                    @change="$emit('update:modelValue', $event.target.checked ? 1 : 0)"
                />
                <label :for="fieldId">{{ fieldLabel }}</label>
                <div v-if="errors.length > 0" class="invalid-feedback">{{ errors[0] }}</div>
            </div>
        </div>
        <div v-if="type === 'radio'" :class="{ 'form-group-translation': locale !== null }">
            <p class="form-label">{{ fieldLabel }}</p>
            <input :name="fieldNameComplete" type="hidden" value="" />
            <div v-for="(label, radioButtonValue) in items" :key="radioButtonValue" class="form-field">
                <input
                    :id="fieldId + '_' + radioButtonValue"
                    :checked="modelValue === radioButtonValue"
                    :class="{ 'is-invalid': errors.length > 0 }"
                    :data-language="locale"
                    :name="fieldNameComplete"
                    :required="required"
                    :type="type"
                    :value="radioButtonValue"
                    :disabled="disabled"
                    class="radio"
                    @change="$emit('update:modelValue', radioButtonValue)"
                />
                <label :for="fieldId + '_' + radioButtonValue">{{ t(label) }}</label>
                <div v-if="errors.length > 0" class="invalid-feedback">{{ errors[0] }}</div>
            </div>
        </div>
        <div v-if="['image', 'document'].includes(type)" :class="{ 'form-group-translation': locale !== null }">
            <p class="form-label">{{ fieldLabel }}</p>
            <input v-if="modelValue === null" :name="fieldNameComplete" :value="null" type="hidden" />
            <input v-if="modelValue !== null" :name="fieldNameComplete + '[id]'" :value="modelValue.id" type="hidden" />
            <input v-if="modelValue !== null" :name="fieldNameComplete + '[path]'" :value="modelValue.path" type="hidden" />
            <input v-if="modelValue !== null" :name="fieldNameComplete + '[extension]'" :value="modelValue.extension" type="hidden" />
            <input v-if="modelValue !== null" :name="fieldNameComplete + '[width]'" :value="modelValue.width" type="hidden" />
            <input v-if="modelValue !== null" :name="fieldNameComplete + '[height]'" :value="modelValue.height" type="hidden" />
            <input v-if="modelValue !== null" :name="fieldNameComplete + '[type]'" :value="modelValue.type" type="hidden" />
            <input v-if="modelValue !== null" :name="fieldNameComplete + '[name]'" :value="modelValue.name" type="hidden" />
            <input v-if="modelValue !== null" :name="fieldNameComplete + '[thumb_sm]'" :value="modelValue.thumb_sm" type="hidden" />
            <div :data-language="locale">
                <div v-if="modelValue !== null" class="filemanager-item filemanager-item-with-name filemanager-item-horizontal filemanager-item-removable">
                    <div class="filemanager-item-wrapper">
                        <div v-if="modelValue.type === 'i'" class="filemanager-item-icon">
                            <img class="filemanager-item-image" :alt="modelValue.alt" :src="modelValue.thumb_sm" />
                        </div>
                        <div v-else :class="'filemanager-item-icon-' + modelValue.type" class="filemanager-item-icon">
                            <file-music-icon v-if="modelValue.type === 'a'" size="72" stroke-width="1.25" />
                            <file-video2-icon v-if="modelValue.type === 'v'" size="72" stroke-width="1.25" />
                            <file-icon v-if="modelValue.type === 'd'" size="72" stroke-width="1.25" />
                            <folder-icon v-if="modelValue.type === 'f'" size="72" stroke-width="1.25" />
                        </div>
                        <div class="filemanager-item-name">{{ modelValue.name }}</div>
                        <button class="filemanager-item-removable-button btn-close" type="button" :aria-label="t('Remove')" @click="remove"></button>
                    </div>
                </div>
                <div v-if="modelValue === null">
                    <button class="filemanager-field-btn-add" type="button" :disabled="disabled" @click="openFilePicker">
                        <circle-plus-icon size="16" />
                        {{ t('Add') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { CirclePlusIcon, FileIcon, FileMusicIcon, FileVideo2Icon, FolderIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    field: {
        type: Object,
        required: true,
    },
    fieldName: {
        type: String,
        required: true,
    },
    index: {
        type: Number,
    },
    modelValue: {
        required: true,
    },
    locale: {
        type: String,
        default: null,
    },
    errors: {
        type: Array,
        required: true,
    },
});
const name = ref(props.field.name);
const title = ref(props.field.title);
const type = ref(props.field.type);
const rows = ref(props.field.rows);
const min = ref(props.field.min);
const max = ref(props.field.max);
const step = ref(props.field.step);
const items = ref(props.field.items);
const required = ref(props.field.required);
const disabled = ref(props.field.disabled);
const readonly = ref(props.field.readonly);
const placeholder = ref(props.field.placeholder);
const choosingFile = ref(props);
const fieldNameComplete = computed(() => {
    let fieldName = props.fieldName + '[' + props.index + '][' + name.value + ']';
    if (props.locale !== null) {
        fieldName += '[' + props.locale + ']';
    }
    return fieldName;
});
const fieldId = computed(() => {
    let id = props.fieldName + '_' + props.index + '_' + name.value;
    if (props.locale !== null) {
        id += '_' + props.locale;
    }
    return id;
});
const fieldLabel = computed(() => {
    let label = t(title.value);
    if (props.locale !== null) {
        label += ' (' + props.locale + ')';
    }
    return label;
});

const emit = defineEmits(['update:modelValue']);

emitter.on('fileAdded', (file) => {
    if (choosingFile.value === true) {
        emit('update:modelValue', file);
    }
    choosingFile.value = false;
});

function remove() {
    emit('update:modelValue', null);
}

function openFilePicker() {
    choosingFile.value = true;
    emitter.emit('openFilePicker', { single: true });
}
</script>
