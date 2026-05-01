<template>
    <div :id="props.id" class="modal fade" tabindex="-1" :aria-labelledby="props.id + '-label'" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" @submit.prevent="save">
                <div class="modal-header">
                    <h1 :id="props.id + '-label'" class="modal-title fs-5">{{ props.title || t('Embed Video') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" :aria-label="t('Close')"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label :for="props.id + '-src'" class="col-form-label">{{ t('URL') }}</label>
                        <input :id="props.id + '-src'" ref="inputElement" v-model="src" type="url" class="form-control" />
                        <small class="form-text text-muted">{{ helpText }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-primary">{{ t('OK') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import Modal from 'bootstrap/js/dist/modal';
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
    videoDialog.value = new Modal('#' + props.id);

    const modal = document.querySelector('#' + props.id);
    modal.addEventListener('shown.bs.modal', () => {
        inputElement.value?.focus();
    });
    modal.addEventListener('hide.bs.modal', () => {
        const buttonElement = document.activeElement;
        buttonElement.blur();
    });
    modal.addEventListener('hidden.bs.modal', () => {
        show.value = false;
        activeElement.value.focus();
    });
});
</script>
