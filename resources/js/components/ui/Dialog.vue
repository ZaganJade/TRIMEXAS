<script setup>
import {
    DialogRoot,
    DialogPortal,
    DialogOverlay,
    DialogContent,
    DialogTitle,
    DialogDescription,
    DialogClose,
} from "radix-vue";
import { X } from "@lucide/vue";
import { cn } from "@/lib/utils";

defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: "" },
    description: { type: String, default: "" },
});
defineEmits(["update:open"]);
</script>

<template>
    <DialogRoot :open="open" @update:open="$emit('update:open', $event)">
        <DialogPortal>
            <DialogOverlay
                class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm data-[state=open]:animate-[fade-in_180ms_var(--ease-soft)]"
            />
            <DialogContent
                :class="cn(
                    'fixed left-1/2 top-1/2 z-50 w-full max-w-lg -translate-x-1/2 -translate-y-1/2',
                    'rounded-[var(--radius-card)] bg-[var(--surface)] border border-[var(--border)]',
                    'shadow-[var(--shadow-card)] p-6',
                    'data-[state=open]:animate-[rise-in_240ms_var(--ease-soft)]'
                )"
            >
                <DialogTitle
                    v-if="title"
                    class="font-display text-lg font-semibold tracking-tight"
                >
                    {{ title }}
                </DialogTitle>
                <DialogDescription
                    v-if="description"
                    class="mt-1 text-sm text-[var(--muted)]"
                >
                    {{ description }}
                </DialogDescription>
                <div class="mt-4">
                    <slot />
                </div>
                <DialogClose
                    class="absolute right-4 top-4 rounded-md text-[var(--muted)] hover:text-[var(--foreground)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)]/40"
                    aria-label="Tutup"
                >
                    <X :size="18" />
                </DialogClose>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
