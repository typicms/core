<template>
    <a :href="url" class="btn-solid theme-secondary btn-xs">{{ $t('Show') }}</a>
</template>

<script setup>
defineProps({
    url: {
        type: String,
        required: true,
    },
});
</script>
