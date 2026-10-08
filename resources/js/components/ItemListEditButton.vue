<template>
    <a :href="url" class="btn-solid theme-secondary btn-xs">{{ $t('Edit') }}</a>
</template>

<script setup>
defineProps({
    url: {
        type: String,
        required: true,
    },
});
</script>
