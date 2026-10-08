<template>
    <ul class="uppy-list">
        <li v-for="file in files" :key="file.id" class="uppy-list-item">
            <div class="uppy-list-item-info">
                <div class="uppy-list-item-status">
                    <div v-if="!file.progress.uploadComplete && !file.error" class="spinner-border fg-secondary spinner-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <CircleCheckIcon v-else-if="file.progress.uploadComplete" :size="18" class="fg-success" />
                    <CircleXIcon v-else-if="file.error" :size="18" class="fg-danger" />
                </div>
                <span class="uppy-list-item-name">{{ file.name }}</span>
                <span class="uppy-list-item-size">{{ formatFileSize(file.size) }}</span>
                <button class="uppy-list-item-button" type="button" @click="removeFile(file.id)">{{ t('Cancel') }}</button>
            </div>
            <div v-if="file.error" class="uppy-list-item-error fg-danger">
                {{ t(file.error) }}
            </div>
            <div
                class="uppy-list-item-progress-bar"
                role="progressbar"
                :style="{ width: file.progress.percentage + '%' }"
                :aria-valuenow="file.progress.percentage"
                aria-valuemin="0"
                aria-valuemax="100"
            ></div>
        </li>
    </ul>
</template>

<script setup>
import { CircleCheckIcon, CircleXIcon } from '@lucide/vue';
import { UppyContextSymbol } from '@uppy/vue';
import { inject, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const uppyContext = inject(UppyContextSymbol);
if (!uppyContext?.uppy) {
    throw new Error('Component must be called within a UppyContextProvider');
}
const uppy = uppyContext.uppy;

const files = ref([]);
const { t } = useI18n();

function updateFiles() {
    files.value = Object.values(uppy.getState().files);
}

function handleComplete(result) {
    setTimeout(() => {
        result.successful.forEach((file) => {
            uppy.removeFile(file.id);
        });
        updateFiles();
    }, 500);
}

onMounted(() => {
    updateFiles();
    uppy.on('file-added', updateFiles);
    uppy.on('file-removed', updateFiles);
    uppy.on('upload-progress', updateFiles);
    uppy.on('upload-success', updateFiles);
    uppy.on('upload-error', updateFiles);
    uppy.on('complete', handleComplete);
});

onUnmounted(() => {
    uppy.off('file-added', updateFiles);
    uppy.off('file-removed', updateFiles);
    uppy.off('upload-progress', updateFiles);
    uppy.off('upload-success', updateFiles);
    uppy.off('upload-error', updateFiles);
    uppy.off('complete', handleComplete);
});

function formatFileSize(bytes) {
    if (bytes === 0) {
        return '0 Bytes';
    }
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

function removeFile(fileId) {
    uppy.removeFile(fileId);
}
</script>
