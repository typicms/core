<template>
    <dialog :id="props.id" class="dialog" :aria-labelledby="props.id + '-label'">
        <form class="d-contents" @submit.prevent="save">
            <div class="dialog-header">
                <h1 :id="props.id + '-label'" class="dialog-title fs-lg">{{ t('Image') }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="dialog" :aria-label="t('Close')"></button>
            </div>
            <div class="dialog-body">
                <div class="mb-3">
                    <label :for="props.id + '-src'" class="col-form-label">{{ t('URL') }}</label>
                    <div class="input-group">
                        <input :id="props.id + '-src'" v-model="src" type="text" class="form-control" />
                        <button type="button" class="btn-sm btn-solid theme-secondary" @click="browseServer">
                            {{ t('Browse server') }}
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label :for="props.id + '-alt'" class="col-form-label">{{ t('Alt attribute') }}</label>
                    <input :id="props.id + '-alt'" v-model="alt" type="text" class="form-control" />
                </div>
                <div class="form-field mt-5">
                    <input :id="props.id + '-captioned'" v-model="captioned" class="check" type="checkbox" />
                    <label :for="props.id + '-captioned'">{{ t('Captioned image') }}</label>
                </div>
                <div class="form-field mb-3">
                    <input :id="props.id + '-custom-size'" v-model="customSize" class="check" type="checkbox" @change="onCustomSizeChange" />
                    <label :for="props.id + '-custom-size'">{{ t('Custom size') }}</label>
                </div>
                <div v-show="customSize" class="row mb-3 gx-5">
                    <div class="col">
                        <label :for="props.id + '-width'" class="col-form-label">{{ t('Width') }}</label>
                        <div class="input-group">
                            <input :id="props.id + '-width'" v-model="width" class="form-control" type="number" min="0" :disabled="!customSize" @input="setHeight" />
                            <span class="input-group-text">px</span>
                        </div>
                    </div>
                    <div class="col">
                        <label :for="props.id + '-height'" class="col-form-label">{{ t('Height') }}</label>
                        <div class="input-group">
                            <input
                                :id="props.id + '-height'"
                                v-model="height"
                                class="form-control"
                                type="number"
                                min="0"
                                :step="baseline"
                                :disabled="!customSize"
                                @input="setWidth"
                                @change="snapHeight"
                            />
                            <span class="input-group-text">px</span>
                        </div>
                    </div>
                </div>
                <div v-show="customSize" class="form-field mb-3">
                    <input :id="props.id + '-constrain'" v-model="constrain" class="check" type="checkbox" />
                    <label :for="props.id + '-constrain'">{{ t('Constrain proportions') }}</label>
                </div>
                <div v-show="customSize" class="mt-5">
                    <label class="form-label">{{ t('Alignment') }}</label>
                    <div class="form-field">
                        <input :id="props.id + '-align-none'" v-model="align" class="radio" type="radio" value="none" />
                        <label :for="props.id + '-align-none'">{{ t('None') }}</label>
                    </div>
                    <div class="form-field">
                        <input :id="props.id + '-align-left'" v-model="align" class="radio" type="radio" value="left" />
                        <label :for="props.id + '-align-left'">{{ t('Left') }}</label>
                    </div>
                    <div class="form-field">
                        <input :id="props.id + '-align-right'" v-model="align" class="radio" type="radio" value="right" />
                        <label :for="props.id + '-align-right'">{{ t('Right') }}</label>
                    </div>
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
import { onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const imageDialog = ref(null);

const src = ref('');
const alt = ref('');
const width = ref('');
const height = ref('');
const originalWidth = ref('');
const originalHeight = ref('');
const customWidth = ref(null);
const customHeight = ref(null);
const ratio = ref(0);
const align = ref('none');
const customSize = ref(false);
const constrain = ref(true);
const baseline = ref(1);

function onCustomSizeChange() {
    if (!customSize.value) {
        align.value = 'none';
        if (width.value && Number(width.value) !== Number(originalWidth.value)) {
            customWidth.value = width.value;
            customHeight.value = height.value;
        }
        width.value = originalWidth.value;
        height.value = originalHeight.value;
        return;
    }

    if (customWidth.value && customHeight.value) {
        width.value = customWidth.value;
        height.value = customHeight.value;
        return;
    }

    if (originalWidth.value) {
        width.value = Math.round(Number(originalWidth.value) / 2);
        height.value = Math.round(Number(originalHeight.value) / 2);
        snapHeight();
    }
}

const activeElement = ref(null);

const image = defineModel('image', { required: true });
const show = defineModel('show', { required: true });
const captioned = defineModel('captioned', { required: true });

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
            baseline.value = getBaseline();
            activeElement.value = document.activeElement;
            imageDialog.value.show();
        } else {
            imageDialog.value.hide();
        }
    },
);

watch(image, (image) => {
    src.value = image.src;
    alt.value = image.alt;
    width.value = image.width;
    height.value = image.height;
    originalWidth.value = image.dataOriginalWidth || image.width || '';
    originalHeight.value = image.dataOriginalHeight || image.height || '';
    customWidth.value = null;
    customHeight.value = null;
    ratio.value = image.width / image.height;
    align.value = image.align || 'none';
    customSize.value = image.customSize || false;
    constrain.value = image.constrain !== false;
});

emitter.on('fileSelected', (file) => {
    src.value = file.storage_url;
    alt.value = file.alt_attribute[props.locale] || '';
    originalWidth.value = file.width || '';
    originalHeight.value = file.height || '';
    customWidth.value = null;
    customHeight.value = null;
    ratio.value = file.width / file.height;
    if (customSize.value) {
        width.value = file.width ? Math.round(file.width / 2) : null;
        height.value = file.height ? Math.round(file.height / 2) : null;
        snapHeight();
    } else {
        width.value = file.width || null;
        height.value = file.height || null;
    }
});

emitter.on('openImageDialog' + props.id, () => {
    show.value = true;
});

function getBaseline() {
    const target = document.querySelector('.rich-content-container') || document.documentElement;
    const styles = getComputedStyle(target);
    const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    const lineHeight = parseFloat(styles.getPropertyValue('--body-line-height')) || 1.5;
    const fontSizeRaw = styles.getPropertyValue('--body-font-size').trim();

    let fontSizePx;
    if (fontSizeRaw.endsWith('rem')) {
        fontSizePx = parseFloat(fontSizeRaw) * rootFontSize;
    } else if (fontSizeRaw.endsWith('px')) {
        fontSizePx = parseFloat(fontSizeRaw);
    } else {
        fontSizePx = parseFloat(fontSizeRaw) || rootFontSize;
    }

    return Math.floor(lineHeight * fontSizePx);
}

function snapToBaseline(value) {
    return Math.max(baseline.value, Math.round(Number(value) / baseline.value) * baseline.value);
}

function setHeight() {
    if (!constrain.value) {
        return;
    }

    if (!width.value || !ratio.value) {
        height.value = '';
        return;
    }

    height.value = snapToBaseline(Number(width.value) / ratio.value);
}

function setWidth() {
    if (!constrain.value) {
        return;
    }

    if (!height.value || !ratio.value) {
        width.value = '';
        return;
    }

    width.value = Math.round(Number(height.value) * ratio.value);
}

function snapHeight() {
    if (!height.value) {
        return;
    }

    height.value = snapToBaseline(height.value);
    setWidth();
}

function browseServer() {
    emitter.emit('openFilePicker', {
        selectSingleFile: true,
        emitOnClose: 'openImageDialog' + props.id,
        type: 'image',
    });
    show.value = false;
}

function save() {
    if (customSize.value) {
        snapHeight();
    }
    show.value = false;
    image.value.src = src.value;
    image.value.alt = alt.value;
    image.value.width = width.value ? Math.round(Number(width.value)) : null;
    image.value.height = height.value ? Math.round(Number(height.value)) : null;
    image.value.dataOriginalWidth = originalWidth.value ? Math.round(Number(originalWidth.value)) : null;
    image.value.dataOriginalHeight = originalHeight.value ? Math.round(Number(originalHeight.value)) : null;
    image.value.align = align.value;
    image.value.customSize = customSize.value;
    image.value.constrain = constrain.value;
    emit('save');
}

onMounted(() => {
    imageDialog.value = new Dialog('#' + props.id);
    baseline.value = getBaseline();

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
