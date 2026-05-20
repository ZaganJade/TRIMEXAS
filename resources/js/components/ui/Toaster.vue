<script setup>
import { Toaster as Sonner } from "vue-sonner";
import "vue-sonner/style.css";
</script>

<template>
    <Sonner
        position="top-right"
        :toast-options="{
            classes: {
                toast:
                    'group relative flex w-full items-center gap-3 rounded-[var(--radius-card)] border border-[var(--border)] bg-[var(--surface)] p-4 text-sm text-[var(--foreground)] shadow-[var(--shadow-card)]',
                title: 'font-medium',
                description: 'text-[var(--muted)] text-xs',
                actionButton: 'rounded-md bg-[var(--primary)] text-[var(--primary-foreground)] px-2 py-1 text-xs',
            },
        }"
    />
</template>
