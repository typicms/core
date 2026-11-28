<template>
    <div class="file-edit-form">
        <div v-if="!model" class="spinner-border spinner-sm fg-body" role="status">
            <span class="visually-hidden">{{ t('Loading…') }}</span>
        </div>

        <div v-else>
            <div v-if="isFolder" class="mb-5">
                <label :for="formId + '-name'" class="form-label">{{ t('Name') }}</label>
                <input :id="formId + '-name'" v-model="form.name" :class="{ 'is-invalid': error('name') }" class="form-control" type="text" autocomplete="off" @keydown.enter.prevent="save()" />
                <div v-if="error('name')" class="invalid-feedback">{{ error('name') }}</div>
            </div>

            <div v-else class="row gx-5">
                <div class="lg:col-6">
                    <template v-for="field in translatableFields" :key="field.name">
                        <div v-for="locale in visibleLocales" :key="field.name + '-' + locale.short" class="mb-5">
                            <label :for="fieldId(field.name, locale.short)" class="form-label">
                                {{ t(field.label) }}
                                <span v-if="locales.length > 1" class="fg-secondary">({{ locale.short }})</span>
                            </label>
                            <textarea
                                v-if="field.textarea"
                                :id="fieldId(field.name, locale.short)"
                                v-model="form[field.name][locale.short]"
                                :class="{ 'is-invalid': error(field.name + '.' + locale.short) }"
                                class="form-control"
                                rows="3"
                            ></textarea>
                            <input
                                v-else
                                :id="fieldId(field.name, locale.short)"
                                v-model="form[field.name][locale.short]"
                                :class="{ 'is-invalid': error(field.name + '.' + locale.short) }"
                                class="form-control"
                                type="text"
                                @keydown.enter.prevent="save()"
                            />
                            <div v-if="error(field.name + '.' + locale.short)" class="invalid-feedback">{{ error(field.name + '.' + locale.short) }}</div>
                        </div>
                    </template>

                    <div class="mb-5">
                        <label :for="formId + '-name'" class="form-label">{{ t('Display name') }}</label>
                        <input
                            :id="formId + '-name'"
                            v-model="form.name"
                            :class="{ 'is-invalid': error('name') }"
                            class="form-control"
                            type="text"
                            autocomplete="off"
                            @keydown.enter.prevent="save()"
                        />
                        <div v-if="error('name')" class="invalid-feedback">{{ error('name') }}</div>
                    </div>

                    <div class="mb-5">
                        <label :for="formId + '-file'" class="form-label">{{ t('Replace file') }}</label>
                        <input :id="formId + '-file'" ref="fileInput" :class="{ 'is-invalid': error('file') }" class="form-control" type="file" @change="onFileChange" />
                        <div v-if="error('file')" class="invalid-feedback">{{ error('file') }}</div>
                    </div>
                </div>

                <div class="lg:col-6">
                    <template v-if="model.type === 'i'">
                        <image-cropper
                            v-if="model.extension !== 'svg'"
                            :key="model.path"
                            :image-url="model.storage_url"
                            :file-id="model.id"
                            :focal-x="form.focal_x"
                            :focal-y="form.focal_y"
                            :reload-after-crop="false"
                            :with-inputs="false"
                            @cropped="onCropped"
                            @focal-change="onFocalChange"
                        ></image-cropper>
                        <img v-else class="img-fluid mb-5" :src="model.storage_url" alt="" />
                    </template>

                    <table class="table">
                        <tbody>
                            <tr>
                                <th class="w-25">{{ t('URL') }}</th>
                                <td>
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <a :href="model.storage_url" target="_blank" rel="noopener noreferrer">{{ model.storage_url }}</a>
                                        <button class="btn-solid theme-secondary btn-xs text-nowrap" type="button" @click="copyUrl">{{ t('Copy') }}</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ t('Path') }}</th>
                                <td>{{ model.path }}</td>
                            </tr>
                            <tr>
                                <th>{{ t('Extension') }}</th>
                                <td>{{ model.extension }}</td>
                            </tr>
                            <tr>
                                <th>{{ t('Mimetype') }}</th>
                                <td>{{ model.mimetype }}</td>
                            </tr>
                            <tr v-if="model.width">
                                <th>{{ t('Width') }}</th>
                                <td>{{ model.width }} px</td>
                            </tr>
                            <tr v-if="model.height">
                                <th>{{ t('Height') }}</th>
                                <td>{{ model.height }} px</td>
                            </tr>
                            <tr>
                                <th>{{ t('Created at') }}</th>
                                <td>{{ formatDate(model.created_at) }}</td>
                            </tr>
                            <tr>
                                <th>{{ t('Updated at') }}</th>
                                <td>{{ formatDate(model.updated_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import fetcher from '../admin/fetcher';
import toast from '../admin/toast.ts';

import ImageCropper from './ImageCropper.vue';

const { t } = useI18n();

const props = defineProps({
    fileId: {
        type: Number,
        required: true,
    },
    locale: {
        type: String,
        default: 'all',
    },
});

const emit = defineEmits(['loaded', 'saved']);

const translatableFields = computed(() => {
    const fields = [
        { name: 'title', label: 'Title' },
        { name: 'description', label: 'Description', textarea: true },
    ];

    if (model.value?.type === 'i') {
        fields.push({ name: 'alt_attribute', label: 'Alt attribute' });
    }

    return fields;
});

const formId = `file-edit-form-${props.fileId}`;
const locales = window.TypiCMS.locales ?? [];
const model = ref(null);
const form = ref(null);
const newFile = ref(null);
const fileInput = ref(null);
const errors = ref({});
const saving = ref(false);

const isFolder = computed(() => model.value?.type === 'f');

const visibleLocales = computed(() => {
    if (props.locale === 'all') {
        return locales;
    }

    return locales.filter((locale) => locale.short === props.locale);
});

defineExpose({ save, saving });

fetchModel();

async function fetchModel() {
    try {
        const response = await fetcher(`/api/files/${props.fileId}`);
        const responseData = await response.json();

        if (!response.ok) {
            throw new Error(responseData.message);
        }

        setModel(responseData);
        emit('loaded', responseData);
    } catch (error) {
        toast.error(error.message || t('Sorry, a network error occurred.'));
    }
}

function setModel(data) {
    model.value = data;
    form.value = {
        name: data.name,
        focal_x: data.focal_x,
        focal_y: data.focal_y,
        title: { ...emptyTranslations(), ...data.translations?.title },
        description: { ...emptyTranslations(), ...data.translations?.description },
        alt_attribute: { ...emptyTranslations(), ...data.translations?.alt_attribute },
    };
}

function emptyTranslations() {
    return Object.fromEntries(locales.map((locale) => [locale.short, '']));
}

function fieldId(name, locale) {
    return `${formId}-${name}-${locale}`;
}

function error(key) {
    return errors.value[key]?.[0] ?? null;
}

function onFileChange(event) {
    newFile.value = event.target.files[0] ?? null;
}

function onFocalChange({ x, y }) {
    form.value.focal_x = x;
    form.value.focal_y = y;
}

function onCropped(file) {
    model.value = { ...model.value, ...file };
    form.value.name = file.name;
    emit('saved', model.value);
}

function formData() {
    const data = new FormData();
    data.append('_method', 'PUT');
    data.append('name', form.value.name ?? '');

    if (isFolder.value) {
        return data;
    }

    translatableFields.value.forEach((field) => {
        locales.forEach((locale) => {
            data.append(`${field.name}[${locale.short}]`, form.value[field.name][locale.short] ?? '');
        });
    });

    if (model.value.type === 'i') {
        data.append('focal_x', form.value.focal_x ?? '');
        data.append('focal_y', form.value.focal_y ?? '');
    }

    if (newFile.value) {
        data.append('file', newFile.value);
    }

    return data;
}

async function save() {
    if (saving.value) {
        return false;
    }

    saving.value = true;
    errors.value = {};

    try {
        const response = await fetch(`/api/files/${props.fileId}`, {
            method: 'POST',
            body: formData(),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]')?.content,
            },
        });
        const responseData = await response.json();

        if (response.status === 422) {
            errors.value = responseData.errors ?? {};
            return false;
        }

        if (!response.ok) {
            throw new Error(responseData.message);
        }

        newFile.value = null;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
        setModel(responseData);
        toast.success(t('Item successfully updated.'));
        emit('saved', responseData);

        return true;
    } catch (error) {
        toast.error(error.message || t('Sorry, an error occurred.'));

        return false;
    } finally {
        saving.value = false;
    }
}

async function copyUrl() {
    await navigator.clipboard.writeText(new URL(model.value.storage_url, window.location.origin).href);
    toast.success(t('Copied to the clipboard'));
}

function formatDate(date) {
    if (!date) {
        return '';
    }

    return new Date(date).toLocaleString(window.TypiCMS.locale_region);
}
</script>
