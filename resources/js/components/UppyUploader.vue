<template>
    <UppyContextProvider :uppy="uppy">
        <UppyList />
        <DropzoneContent ref="dropzoneRef" />
    </UppyContextProvider>
</template>

<script setup>
import Uppy from '@uppy/core';
import DropTarget from '@uppy/drop-target';
import es from '@uppy/locales/lib/es_ES';
import fr from '@uppy/locales/lib/fr_FR';
import nl from '@uppy/locales/lib/nl_NL';
import { UppyContextProvider, useDropzone, useFileInput } from '@uppy/vue';
import XHRUpload from '@uppy/xhr-upload';
import { computed, defineComponent, h, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import UppyList from './UppyList.vue';

const props = defineProps({
    folderId: {
        type: [String, Number],
        default: '',
    },
});

const maxFilesize = ref(window.TypiCMS.max_file_upload_size);
const uppyLocales = { fr, nl, es };
const { t } = useI18n();
const emit = defineEmits(['complete']);
const dropzoneRef = ref(null);

const DropzoneContent = defineComponent({
    setup(props, { expose }) {
        const { getRootProps, getInputProps } = useDropzone({
            noClick: true,
        });
        const { getButtonProps, getInputProps: getFileInputProps } = useFileInput();

        expose({
            getButtonProps,
        });

        return () =>
            h('div', [h('input', { ...getInputProps(), class: 'd-none' }), h('div', { ...getRootProps(), role: 'button' }, [h('div', [h('input', { ...getFileInputProps(), class: 'd-none' })])])]);
    },
});

const uppy = computed(() =>
    new Uppy({
        autoProceed: true,
        locale: uppyLocales[TypiCMS.locale],
        restrictions: {
            maxFileSize: maxFilesize.value * 1024,
            allowedFileTypes: [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'application/vnd.openxmlformats-officedocument.presentationml.slide',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                'application/vnd.openxmlformats-officedocument.presentationml.template',
                'application/msword',
                'application/vnd.ms-powerpoint',
                'application/vnd.ms-excel',
                'application/pdf',
                'application/postscript',
                'application/zip',
                'application/json',
                'text/plain',
                'image/svg+xml',
                'image/tiff',
                'image/jpeg',
                'image/gif',
                'image/png',
                'image/bmp',
                'image/gif',
                'audio/*',
                'video/*',
            ],
        },
    })
        .use(DropTarget, {
            target: document.body,
        })
        .use(XHRUpload, {
            endpoint: '/api/files',
            formData: true,
            fieldName: 'name',
            allowedMetaFields: ['folder_id'],
            headers: {
                Accept: 'application/json',
                Authorization: 'Bearer ' + document.head.querySelector('meta[name="api-token"]').content,
            },
        })
        .on('file-added', (file) => {
            uppy.value.setFileMeta(file.id, {
                folder_id: file.meta.folder_id ?? props.folderId,
            });
        })
        .on('restriction-failed', (file, error) => {
            alertify.error(error.message);
        })
        .on('complete', (result) => {
            const fails = result.failed;
            if (fails.length > 0) {
                alertify.error(
                    t('# files could not be uploaded.', fails.length, {
                        count: fails.length,
                    }),
                );
            }

            const successes = result.successful;
            if (successes.length > 0) {
                alertify.success(
                    t('# files uploaded.', successes.length, {
                        count: successes.length,
                    }),
                );
                emit('complete');
            }
        }),
);

function addFilesToFolder(files, folderId) {
    files.forEach((file) => {
        try {
            uppy.value.addFile({
                name: file.name,
                type: file.type,
                data: file,
                meta: { folder_id: folderId },
            });
        } catch {
            // Restriction errors are already reported by the restriction-failed listener.
        }
    });
}

defineExpose({
    dropzoneRef,
    addFilesToFolder,
});
</script>

<style src="@uppy/vue/css/style.css"></style>
