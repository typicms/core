<template>
    <div id="filemanager">
        <dialog v-if="modal" id="filemanager-modal" class="filemanager-dialog dialog dialog-xl" aria-labelledby="filemanagerLabel">
            <file-manager-content :single="options.single" :type="options.type" :select-single-file="options.selectSingleFile" :multiple="options.multiple" :modal="modal"></file-manager-content>
        </dialog>
        <div v-else>
            <file-manager-content :single="options.single" :type="options.type" :select-single-file="options.selectSingleFile" :multiple="options.multiple"></file-manager-content>
        </div>
    </div>
</template>

<script setup>
import Dialog from 'bootstrap/js/dist/dialog.js';
import { onMounted, ref, watch } from 'vue';

import FileManagerContent from './FileManagerContent.vue';

const filePickerModal = ref(null);
const show = defineModel('show', { default: false });

const props = defineProps({
    modal: {
        type: Boolean,
        default: true,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    single: {
        type: Boolean,
        default: false,
    },
});

const options = ref({
    modal: props.modal,
    multiple: props.multiple,
    single: props.single,
    selectSingleFile: false,
    type: null,
    emitOnClose: null,
});

watch(
    () => show.value,
    (show) => {
        if (show) {
            filePickerModal.value.show();
        } else {
            filePickerModal.value.hide();
        }
    },
);

emitter.on('openFilePicker', (opts) => {
    options.value = opts;
    show.value = true;
});

emitter.on('closeModal', () => {
    closeModal();
});

function closeModal() {
    show.value = false;
}

onMounted(() => {
    filePickerModal.value = new Dialog('#filemanager-modal');

    const modal = document.querySelector('#filemanager-modal');
    modal.addEventListener('hide.bs.dialog', () => {
        document.activeElement.blur();
        show.value = false;
        if (options.value.emitOnClose) {
            emitter.emit(options.value.emitOnClose);
        }
    });
});
</script>
