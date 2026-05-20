<script setup>
import { computed } from "vue";
import { cva } from "class-variance-authority";
import { Link } from "@inertiajs/vue3";
import { cn } from "@/lib/utils";

const props = defineProps({
    variant: {
        type: String,
        default: "primary",
        validator: (v) =>
            ["primary", "secondary", "ghost", "outline", "link", "danger"].includes(v),
    },
    size: {
        type: String,
        default: "md",
        validator: (v) => ["sm", "md", "lg", "icon"].includes(v),
    },
    as: {
        type: String,
        default: "button",
    },
    href: { type: String, default: null },
    type: { type: String, default: "button" },
    disabled: { type: Boolean, default: false },
});

const variants = cva(
    [
        "inline-flex items-center justify-center gap-2",
        "font-medium tracking-tight",
        "transition-[transform,box-shadow,background-color,color] duration-200",
        "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2",
        "focus-visible:ring-offset-[var(--background)]",
        "disabled:opacity-50 disabled:pointer-events-none",
        "active:translate-y-px",
    ].join(" "),
    {
        variants: {
            variant: {
                primary: [
                    "bg-[var(--primary)] text-[var(--primary-foreground)]",
                    "hover:bg-[#256B9C] shadow-[0_1px_0_0_rgba(255,255,255,0.12)_inset,0_8px_24px_-12px_rgba(49,137,198,0.5)]",
                ].join(" "),
                secondary: [
                    "bg-[var(--surface)] text-[var(--foreground)]",
                    "border border-[var(--border)]",
                    "hover:bg-[var(--primary-soft)] hover:text-[var(--primary)] hover:border-[var(--primary)]/40",
                ].join(" "),
                outline: [
                    "border border-[var(--primary)] text-[var(--primary)]",
                    "hover:bg-[var(--primary)] hover:text-[var(--primary-foreground)]",
                ].join(" "),
                ghost: [
                    "text-[var(--foreground)]",
                    "hover:bg-[var(--primary-soft)] hover:text-[var(--primary)]",
                ].join(" "),
                link: [
                    "text-[var(--primary)] underline-offset-4 hover:underline px-0",
                ].join(" "),
                danger: [
                    "bg-[#EF4444] text-white hover:bg-[#dc2626]",
                ].join(" "),
            },
            size: {
                sm: "h-9 rounded-[var(--radius-input)] px-3 text-sm",
                md: "h-10 rounded-[var(--radius-card)] px-4 text-sm",
                lg: "h-12 rounded-[var(--radius-card)] px-6 text-base",
                icon: "h-10 w-10 rounded-[var(--radius-card)]",
            },
        },
    }
);

const tag = computed(() => (props.href ? Link : props.as));
const classes = computed(() =>
    cn(variants({ variant: props.variant, size: props.size }))
);
</script>

<template>
    <component
        :is="tag"
        :href="href ?? undefined"
        :type="href ? undefined : type"
        :disabled="disabled"
        :class="classes"
    >
        <slot />
    </component>
</template>
