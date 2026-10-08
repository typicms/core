<template>
    <dialog :id="props.id" class="dialog" :aria-labelledby="props.id + '-label'">
        <form class="d-contents" @submit.prevent="save">
            <div class="dialog-header">
                <h1 :id="props.id + '-label'" class="dialog-title fs-lg">{{ t('Link') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="dialog" :aria-label="t('Close')"></button>
            </div>
            <div class="dialog-body">
                <div class="mb-3">
                    <label :for="props.id + '-type'" class="col-form-label">{{ t('Link type') }} </label>
                    <select :id="props.id + '-type'" v-model="type" class="form-control">
                        <option value="url">{{ t('URL') }}</option>
                        <option value="page">{{ t('Local page') }}</option>
                        <option value="email">{{ t('E-mail') }}</option>
                        <option value="phone">{{ t('Phone') }}</option>
                    </select>
                </div>
                <div v-if="type === 'url'" class="mb-3">
                    <label :for="props.id + '-url'" class="col-form-label">{{ t('URL') }}</label>
                    <div class="input-group">
                        <input :id="props.id + '-url'" v-model="url" type="url" class="form-control" required />
                        <button type="button" class="btn-sm btn-solid theme-secondary" @click="browseServer">
                            {{ t('Browse server') }}
                        </button>
                    </div>
                </div>
                <div v-if="type === 'page'" class="mb-3">
                    <label :for="props.id + '-page'" class="col-form-label">
                        {{ t('Select a page') }}
                        <div v-if="loading" class="spinner-border spinner-sm fg-body ms-3" role="status">
                            <span class="visually-hidden">{{ t('Loading…') }}</span>
                        </div>
                    </label>
                    <input :id="props.id + '-page'" v-model="pageTitle" :list="props.id + '-page-list'" class="form-control" required />
                    <datalist :id="props.id + '-page-list'">
                        <option v-for="page in pages" :key="page[1]" :value="page[0]"></option>
                    </datalist>
                </div>
                <div v-if="type === 'email'" class="mb-3">
                    <div class="mb-3">
                        <label :for="props.id + '-email'" class="col-form-label">{{ t('E-mail') }}</label>
                        <input :id="props.id + '-email'" v-model="email" type="email" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label :for="props.id + '-email-subject'" class="col-form-label">{{ t('Subject') }}</label>
                        <input :id="props.id + '-email-subject'" v-model="emailSubject" type="text" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label :for="props.id + '-email-body'" class="col-form-label">{{ t('Body') }}</label>
                        <textarea :id="props.id + '-email-body'" v-model="emailBody" type="text" class="form-control" />
                    </div>
                </div>
                <div v-if="type === 'phone'" class="mb-3">
                    <label :for="props.id + '-phone'" class="col-form-label">{{ t('Phone') }}</label>
                    <input :id="props.id + '-phone'" v-model="phone" type="tel" class="form-control" required />
                </div>
                <div v-if="type === 'url' || type === 'page'" class="form-field mt-5">
                    <input :id="props.id + '-open-in-new-tab'" v-model="newTab" class="check" type="checkbox" />
                    <label :for="props.id + '-open-in-new-tab'">{{ t('Open in new tab') }}</label>
                </div>
            </div>
            <div class="dialog-footer">
                <button type="button" class="btn-sm btn-solid theme-secondary" data-bs-dismiss="dialog">{{ t('Cancel') }}</button>
                <button type="submit" class="btn-sm btn-solid theme-primary">{{ t('OK') }}</button>
            </div>
        </form>
    </dialog>
</template>

<script setup>
import alertify from 'alertify.js';
import Dialog from 'bootstrap/js/dist/dialog.js';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

import fetcher from '../../admin/fetcher.js';

const { t } = useI18n();

const linkDialog = ref(null);
const loadingTimeout = ref(null);
const loading = ref(false);
const pages = ref([]);
const type = ref('url');

const newTab = ref(false);

const url = ref('');
const email = ref('');
const emailSubject = ref('');
const emailBody = ref('');
const phone = ref('');
const page = ref('');

const pageTitle = computed({
    get() {
        if (!page.value) {
            return '';
        }
        const foundPage = pages.value.find((p) => p[1] === page.value);
        return foundPage ? foundPage[0] : '';
    },
    set(newTitle) {
        const foundPage = pages.value.find((p) => p[0] === newTitle);
        if (foundPage) {
            page.value = foundPage[1];
        }
    },
});

const activeElement = ref(null);

const link = defineModel('link', { required: true });
const show = defineModel('show', { required: true });

const props = defineProps({
    id: {
        type: String,
    },
});

const emit = defineEmits(['save']);

watch(
    () => show.value,
    (show) => {
        if (show) {
            activeElement.value = document.activeElement;
            linkDialog.value.show();
        } else {
            linkDialog.value.hide();
        }
    },
);

watch(link, (link) => {
    let href = link.href;
    const target = link.target;
    if (target === '_blank') {
        newTab.value = true;
    } else {
        newTab.value = false;
    }

    // Decode URL-encoded hrefs (e.g., {!!%20page:49%20!!} → {!! page:49 !!})
    if (href) {
        try {
            href = decodeURIComponent(href);
        } catch (e) {
            // If decoding fails, use the original href
        }
    }

    if (!href) {
        type.value = 'url';
        url.value = '';
    } else if (href.startsWith('mailto:')) {
        type.value = 'email';
        newTab.value = false;
        const url = new URL(href);
        email.value = url.pathname;
        emailSubject.value = url.searchParams.get('subject');
        emailBody.value = url.searchParams.get('body');
    } else if (href.startsWith('tel:')) {
        type.value = 'phone';
        newTab.value = false;
        const url = new URL(href);
        phone.value = url.pathname;
    } else if (href.startsWith('{!!')) {
        type.value = 'page';
        page.value = href;
    } else {
        type.value = 'url';
        url.value = href;
    }
});

emitter.on('fileSelected', (file) => {
    url.value = file.storage_url;
});

emitter.on('openLinkDialog' + props.id, () => {
    show.value = true;
});

function browseServer() {
    emitter.emit('openFilePicker', { selectSingleFile: true, emitOnClose: 'openLinkDialog' + props.id });
    show.value = false;
}

function save() {
    show.value = false;
    link.value.target = newTab.value === true ? '_blank' : null;
    link.value.rel = newTab.value === true ? 'noopener noreferrer' : null;
    if (type.value === 'url' && url.value) {
        link.value.href = url.value;
    }
    if (type.value === 'email' && email.value) {
        const mailtoUrl = new URL('mailto:' + email.value);
        if (emailSubject.value) {
            mailtoUrl.searchParams.append('subject', emailSubject.value);
        }
        if (emailBody.value) {
            mailtoUrl.searchParams.append('body', emailBody.value);
        }
        link.value.href = mailtoUrl.toString().replaceAll('+', '%20');
    }
    if (type.value === 'phone' && phone.value) {
        link.value.href = 'tel:' + phone.value;
    }
    if (type.value === 'page' && page.value) {
        link.value.href = page.value;
    }
    emit('save');
}

async function fetchData() {
    startLoading();
    try {
        const response = await fetcher('/api/pages/links-for-editor');
        if (!response.ok) {
            const responseData = await response.json();
            throw new Error(responseData.message);
        }
        pages.value = await response.json();
    } catch (error) {
        alertify.error(t(error.message) || t('An error occurred with the data fetch.'));
    }
    stopLoading();
}

function startLoading() {
    loadingTimeout.value = setTimeout(() => {
        loading.value = true;
    }, 300);
}

function stopLoading() {
    clearTimeout(loadingTimeout.value);
    loading.value = false;
}

onMounted(() => {
    fetchData();
    linkDialog.value = new Dialog('#' + props.id);

    const modal = document.querySelector('#' + props.id);
    modal.addEventListener('hide.bs.dialog', () => {
        const buttonElement = document.activeElement;
        buttonElement.blur();
    });
    modal.addEventListener('hidden.bs.dialog', () => {
        show.value = false;
        activeElement.value.focus();
    });
});
</script>
