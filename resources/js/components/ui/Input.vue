<script setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps({
    modelValue: { type: [String, Number], default: "" },
    type: { type: String, default: "text" },
    placeholder: { type: String, default: "" },
    invalid: { type: Boolean, default: false },
});
const emit = defineEmits(["update:modelValue"]);

const classes = computed(() =>
    cn(
        "h-10 w-full rounded-[var(--radius-input)] px-3 text-sm",
        "bg-[var(--surface)] text-[var(--foreground)]",
        "border border-[var(--border)]",
        "placeholder:text-[var(--muted)]",
        "transition-[box-shadow,border-color] duration-150",
        "focus-visible:outline-none focus-visible:border-[var(--primary)]",
        "focus-visible:ring-2 focus-visible:ring-[var(--ring)]/30",
        props.invalid && "border-[#EF4444] focus-visible:border-[#EF4444] focus-visible:ring-[#EF4444]/30"
    )
);
</script>

<template>
    <input
        :type="type"
        :placeholder="placeholder"
        :value="modelValue"
        :class="classes"
        @input="emit('update:modelValue', $event.target.value)"
    />
</template>
