<script setup>
/**
 * Shared atmospheric backdrop used across authenticated pages.
 * Pure-CSS aurora blobs + grid fade + grain overlay (cheap, no canvas)
 * so dashboards stay fast while matching the landing aesthetic.
 */
defineProps({
    grid: { type: Boolean, default: true },
});
</script>

<template>
    <div
        class="bg-noise pointer-events-none fixed inset-0 -z-10 overflow-hidden"
        aria-hidden="true"
    >
        <div class="aurora aurora-a"></div>
        <div class="aurora aurora-b"></div>
        <div class="aurora aurora-c"></div>
        <div v-if="grid" class="absolute inset-0 bg-grid-fade"></div>
    </div>
</template>
