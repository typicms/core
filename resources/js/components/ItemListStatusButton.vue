<template>
    <button class="btn-status" type="button" @click="emitter.emit('toggleStatus', model)">
        <span :class="statusOn ? 'btn-status-icon-on' : 'btn-status-icon-off'" class="btn-status-icon"></span>
    </button>
</template>

<script setup>
import { computed } from 'vue';

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
