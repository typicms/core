<template>
    <div v-if="imageUrl" class="image-cropper-launcher">
        <img class="image-cropper-preview img-fluid mb-3" :src="imageUrl" alt="" />
        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" :data-bs-target="'#' + modalId">
            <crop-icon :size="18" stroke-width="2" />
            {{ t('Crop image') }}
        </button>

        <div :id="modalId" ref="modalElement" class="modal fade" tabindex="-1" :aria-labelledby="modalId + '-label'" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 :id="modalId + '-label'" class="modal-title fs-5">{{ t('Crop image') }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" :aria-label="t('Close')"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="show" class="image-cropper">
                            <div class="image-cropper-container">
                                <cropper-canvas ref="cropperCanvas" background>
                                    <cropper-image ref="cropperImage" :src="imageUrl" alt="Image to crop" rotatable scalable skewable translatable></cropper-image>
                                    <cropper-shade hidden></cropper-shade>
                                    <cropper-handle action="select" plain></cropper-handle>
                                    <cropper-selection ref="cropperSelection" movable resizable @change="onSelectionChange">
                                        <cropper-grid role="grid" covered></cropper-grid>
                                        <cropper-crosshair centered></cropper-crosshair>
                                        <cropper-handle action="move" theme-color="rgba(255, 255, 255, 0.35)"></cropper-handle>
                                        <cropper-handle action="n-resize"></cropper-handle>
                                        <cropper-handle action="e-resize"></cropper-handle>
                                        <cropper-handle action="s-resize"></cropper-handle>
                                        <cropper-handle action="w-resize"></cropper-handle>
                                        <cropper-handle action="ne-resize"></cropper-handle>
                                        <cropper-handle action="nw-resize"></cropper-handle>
                                        <cropper-handle action="se-resize"></cropper-handle>
                                        <cropper-handle action="sw-resize"></cropper-handle>
                                    </cropper-selection>
                                </cropper-canvas>
                            </div>
                            <div class="image-cropper-actions">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light" @click="rotate(-90)">
                                        <rotate-ccw-icon :size="18" stroke-width="2" />
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light" @click="rotate(90)">
                                        <rotate-cw-icon :size="18" stroke-width="2" />
                                    </button>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-light" @click="flipHorizontal">
                                        <flip-horizontal-icon :size="18" stroke-width="2" />
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light" @click="flipVertical">
                                        <flip-vertical-icon :size="18" stroke-width="2" />
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm btn-light" @click="reset">
                                    {{ t('Reset') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <small class="text-secondary me-auto">{{ t('This will overwrite the original file.') }}</small>
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ t('Cancel') }}</button>
                        <button type="button" class="btn btn-sm btn-primary" @click="saveCroppedImage">{{ t('Save cropped image') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import 'cropperjs';
import { CropIcon, FlipHorizontalIcon, FlipVerticalIcon, RotateCcwIcon, RotateCwIcon } from '@lucide/vue';
import Modal from 'bootstrap/js/dist/modal';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    imageUrl: {
        type: String,
        required: true,
    },
    fileId: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['cropped']);

const modalId = `image-cropper-modal-${props.fileId}`;
const modalElement = ref(null);
const cropperCanvas = ref(null);
const cropperImage = ref(null);
const cropperSelection = ref(null);
const show = ref(false);

let modalInstance = null;
let resizeTimeout = null;

function onShown() {
    show.value = true;
    nextTick(() => {
        cropperImage.value?.$ready(() => initSelectionInsideImage());
    });
    window.addEventListener('resize', onResize);
}

function onResize() {
    if (!show.value) {
        return;
    }
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        cropperImage.value?.$resetTransform();
        cropperImage.value?.$center('contain');
        nextTick(() => initSelectionInsideImage());
    }, 100);
}

function initSelectionInsideImage() {
    const bounds = imageBounds();

    if (!bounds || !bounds.width || !bounds.height || !cropperSelection.value) {
        return;
    }

    const inset = 0.05;
    cropperSelection.value.$change(
        bounds.x + bounds.width * inset,
        bounds.y + bounds.height * inset,
        bounds.width * (1 - inset * 2),
        bounds.height * (1 - inset * 2),
    );
}

function onHidden() {
    show.value = false;
    window.removeEventListener('resize', onResize);
    clearTimeout(resizeTimeout);
}

onMounted(() => {
    if (!modalElement.value) {
        return;
    }

    modalInstance = Modal.getOrCreateInstance(modalElement.value);
    modalElement.value.addEventListener('shown.bs.modal', onShown);
    modalElement.value.addEventListener('hidden.bs.modal', onHidden);
});

onBeforeUnmount(() => {
    if (modalElement.value) {
        modalElement.value.removeEventListener('shown.bs.modal', onShown);
        modalElement.value.removeEventListener('hidden.bs.modal', onHidden);
    }
    window.removeEventListener('resize', onResize);
    clearTimeout(resizeTimeout);
    modalInstance?.dispose();
    modalInstance = null;
});

function imageBounds() {
    if (!cropperCanvas.value || !cropperImage.value) {
        return null;
    }

    const canvasRect = cropperCanvas.value.getBoundingClientRect();
    const imageRect = cropperImage.value.getBoundingClientRect();

    return {
        x: imageRect.left - canvasRect.left,
        y: imageRect.top - canvasRect.top,
        width: imageRect.width,
        height: imageRect.height,
    };
}

function selectionFitsIn(selection, bounds) {
    return selection.x >= bounds.x && selection.y >= bounds.y && selection.x + selection.width <= bounds.x + bounds.width && selection.y + selection.height <= bounds.y + bounds.height;
}

function onSelectionChange(event) {
    const bounds = imageBounds();

    if (!bounds || !bounds.width || !bounds.height) {
        return;
    }

    if (!selectionFitsIn(event.detail, bounds)) {
        event.preventDefault();
    }
}

function rotate(degrees) {
    cropperImage.value?.$rotate(`${degrees}deg`);
}

function flipHorizontal() {
    cropperImage.value?.$scale(-1, 1);
}

function flipVertical() {
    cropperImage.value?.$scale(1, -1);
}

function reset() {
    cropperImage.value?.$resetTransform();
    cropperImage.value?.$center('contain');
    nextTick(() => initSelectionInsideImage());
}

async function saveCroppedImage() {
    if (!cropperSelection.value) {
        return;
    }

    const canvas = await cropperSelection.value.$toCanvas();

    canvas.toBlob(async (blob) => {
        const formData = new FormData();
        formData.append('cropped_image', blob, 'cropped.jpg');
        formData.append('_method', 'POST');

        try {
            const response = await fetch(`/admin/files/${props.fileId}/crop`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    Accept: 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                alertify.success(t('Image cropped successfully'));
                emit('cropped', data);
                window.location.reload();
            } else {
                alertify.error(t('Error cropping image'));
            }
        } catch (error) {
            console.error('Error:', error);
            alertify.error(t('Error cropping image'));
        }
    }, 'image/jpeg');
}
</script>
