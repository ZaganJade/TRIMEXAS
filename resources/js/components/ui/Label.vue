<script setup>
import { cn } from "@/lib/utils";

defineProps({
    for: { type: String, default: null },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <label
        :for="$props.for"
        :class="cn('text-sm font-medium text-[var(--foreground)] tracking-tight')"
    >
        <slot />
        <span v-if="required" class="text-[#EF4444] ml-0.5">*</span>
    </label>
</template>
