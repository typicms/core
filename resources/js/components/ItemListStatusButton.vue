<template>
    <div class="switch">
        <input :checked="statusOn" :aria-label="t('Published')" role="switch" switch type="checkbox" @change="emitter.emit('toggleStatus', model)" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    model: {
        type: Object,
        required: true,
    },
});

const statusOn = computed(() => {
    const raw = typeof props.model.status_translated === 'undefined' ? props.model.status : props.model.status_translated;

    return Number(raw) === 1;
});
</script>
