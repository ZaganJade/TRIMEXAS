<script setup>
import { computed } from "vue";
import { cn } from "@/lib/utils";

const props = defineProps({
    variant: {
        type: String,
        default: "default",
        validator: (v) => ["default", "elevated", "outline", "ghost"].includes(v),
    },
});

const classes = computed(() =>
    cn(
        "rounded-[var(--radius-card)]",
        props.variant === "default" &&
            "bg-[var(--card)] border border-[var(--border)]",
        props.variant === "elevated" &&
            "bg-[var(--card)] shadow-[var(--shadow-card)] border border-[var(--border)]/60",
        props.variant === "outline" &&
            "bg-transparent border border-[var(--border)]",
        props.variant === "ghost" && "bg-[var(--primary-soft)]"
    )
);
</script>

<template>
    <div :class="classes"><slot /></div>
</template>
