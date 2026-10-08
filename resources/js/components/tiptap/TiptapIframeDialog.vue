<template>
    <dialog :id="props.id" class="dialog" :aria-labelledby="props.id + '-label'">
        <form class="d-contents" @submit.prevent="save">
            <div class="dialog-header">
                <h1 :id="props.id + '-label'" class="dialog-title fs-lg">{{ props.title || t('Embed Video') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="dialog" :aria-label="t('Close')"></button>
            </div>
            <div class="dialog-body">
                <div class="mb-3">
                    <label :for="props.id + '-src'" class="col-form-label">{{ t('URL') }}</label>
                    <input :id="props.id + '-src'" ref="inputElement" v-model="src" type="url" class="form-control" />
                    <small class="form-text fg-secondary">{{ helpText }}</small>
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
import Dialog from 'bootstrap/js/dist/dialog.js';
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const videoDialog = ref(null);
const inputElement = ref(null);

const src = ref('');

const activeElement = ref(null);

const video = defineModel('video', { required: true });
const show = defineModel('show', { required: true });

const props = defineProps({
    id: {
        type: String,
    },
    title: {
        type: String,
        default: null,
    },
});

const helpText = computed(() => {
    if (props.title && props.title.includes('YouTube')) {
        return t('Enter a YouTube video or playlist URL.');
    }
    return t('Enter any iframe embed URL (Vimeo, Google Maps, etc.).');
});

const emit = defineEmits(['save']);

watch(
    () => show.value,
    (show) => {
        if (show) {
            activeElement.value = document.activeElement;
            videoDialog.value.show();
        } else {
            videoDialog.value.hide();
        }
    },
);

watch(video, (video) => {
    src.value = video.src;
});

emitter.on('openVideoDialog' + props.id, () => {
    show.value = true;
});

function normalizeYoutubeUrl(url) {
    if (!url) {
        return url;
    }

    // Check for playlist-only URL
    const playlistMatch = url.match(/youtube\.com\/playlist\?list=([a-zA-Z0-9_-]+)/);
    if (playlistMatch) {
        return `https://www.youtube.com/playlist?list=${playlistMatch[1]}`;
    }

    // Check for video with playlist
    const videoWithPlaylistMatch = url.match(/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+).*[&?]list=([a-zA-Z0-9_-]+)/);
    if (videoWithPlaylistMatch) {
        return `https://www.youtube.com/watch?v=${videoWithPlaylistMatch[1]}&list=${videoWithPlaylistMatch[2]}`;
    }

    const patterns = [/youtube\.com\/live\/([a-zA-Z0-9_-]+)/, /youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/, /youtu\.be\/([a-zA-Z0-9_-]+)/, /youtube\.com\/embed\/([a-zA-Z0-9_-]+)/];

    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) {
            return `https://www.youtube.com/watch?v=${match[1]}`;
        }
    }

    return url;
}

function save() {
    show.value = false;
    const isYoutube = props.title && props.title.includes('YouTube');
    video.value.src = isYoutube ? normalizeYoutubeUrl(src.value) : src.value;
    emit('save');
}

onMounted(() => {
    videoDialog.value = new Dialog('#' + props.id);

    const modal = document.querySelector('#' + props.id);
    modal.addEventListener('shown.bs.dialog', () => {
        inputElement.value?.focus();
    });
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
