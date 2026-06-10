<script setup>
import {
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectIcon,
    SelectPortal,
    SelectContent,
    SelectViewport,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
} from "radix-vue";
import { ChevronDown, Check } from "@lucide/vue";
import { cn } from "@/lib/utils";

const props = defineProps({
    modelValue: { type: [String, Number, null], default: null },
    placeholder: { type: String, default: "Pilih..." },
    options: {
        type: Array,
        default: () => [],
    },
});
defineEmits(["update:modelValue"]);

function normalizeOptions(opts) {
    return (opts || []).map((o) =>
        typeof o === "object" && o !== null
            ? { value: o.value, label: o.label ?? String(o.value) }
            : { value: o, label: String(o) }
    );
}
</script>

<template>
    <SelectRoot
        :model-value="modelValue?.toString()"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <SelectTrigger
            :class="cn(
                'inline-flex h-10 w-full items-center justify-between gap-2 px-3',
                'rounded-[var(--radius-input)] bg-[var(--surface)] text-sm',
                'border border-[var(--border)]',
                'data-[state=open]:border-[var(--primary)]',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)]/30'
            )"
        >
            <SelectValue :placeholder="placeholder" />
            <SelectIcon><ChevronDown :size="16" class="text-[var(--muted)]" /></SelectIcon>
        </SelectTrigger>
        <SelectPortal>
            <SelectContent
                :class="cn(
                    'z-50 overflow-hidden rounded-[var(--radius-card)] bg-[var(--surface)]',
                    'border border-[var(--border)] shadow-[var(--shadow-card)]'
                )"
                position="popper"
                :side-offset="4"
            >
                <SelectViewport class="p-1">
                    <SelectItem
                        v-for="opt in normalizeOptions(options)"
                        :key="opt.value"
                        :value="String(opt.value)"
                        class="relative flex h-9 cursor-default select-none items-center rounded-md px-7 text-sm outline-none data-[highlighted]:bg-[var(--primary-soft)] data-[highlighted]:text-[var(--primary)]"
                    >
                        <SelectItemIndicator class="absolute left-2 inline-flex items-center">
                            <Check :size="14" />
                        </SelectItemIndicator>
                        <SelectItemText>{{ opt.label }}</SelectItemText>
                    </SelectItem>
                </SelectViewport>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>
