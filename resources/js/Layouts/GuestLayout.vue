<script setup>
/**
 * Guest layout — centered card surface for auth pages (login/register),
 * with the shared atmospheric backdrop + theme toggle, no app nav.
 */
import { Link } from "@inertiajs/vue3";
import { SunMedium, MoonStar, ArrowLeft } from "@lucide/vue";
import AppAtmosphere from "@/components/AppAtmosphere.vue";
import { useTheme } from "@/composables/useTheme";

defineProps({
    maxWidth: { type: String, default: "max-w-md" },
});

const { isDark, toggleTheme } = useTheme();
</script>

<template>
    <div class="relative flex min-h-screen flex-col overflow-x-clip bg-[var(--background)] text-[var(--foreground)]">
        <AppAtmosphere />

        <header class="relative z-10 mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-5 lg:px-8">
            <Link href="/" class="group flex items-center gap-2.5">
                <img src="/Trimexas-logo.png" alt="Trimexas" class="h-7 w-auto" />
            </Link>

            <div class="flex items-center gap-2">
                <Link href="/" class="btn-ghost !px-4 !py-2 text-[0.82rem]">
                    <ArrowLeft :size="14" />
                    <span class="hidden sm:inline">Beranda</span>
                </Link>
                <button
                    type="button"
                    class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] bg-[var(--surface)] text-[var(--muted)] transition-colors hover:border-[var(--primary)] hover:text-[var(--primary)]"
                    :aria-label="isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap'"
                    @click="toggleTheme"
                >
                    <SunMedium v-if="isDark" :size="15" class="theme-icon is-dark" />
                    <MoonStar v-else :size="15" class="theme-icon" />
                </button>
            </div>
        </header>

        <div class="relative z-10 flex flex-1 items-center justify-center px-4 py-8">
            <div class="w-full" :class="maxWidth">
                <slot />
            </div>
        </div>
    </div>
</template>
