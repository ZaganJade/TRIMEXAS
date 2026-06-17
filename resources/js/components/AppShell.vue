<script setup>
/**
 * Shared authenticated app shell — glass top nav, atmospheric backdrop,
 * theme toggle, notifications bell, logout. Matches the landing aesthetic
 * (dark-first electric-blue) so every authenticated page feels connected.
 */
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch, onMounted, nextTick } from "vue";
import { SunMedium, MoonStar, LogOut } from "@lucide/vue";
import AppAtmosphere from "@/components/AppAtmosphere.vue";
import BellDropdown from "@/components/Notifications/BellDropdown.vue";
import { useTheme } from "@/composables/useTheme";

const props = defineProps({
    // [{ label, route, active }]
    nav: { type: Array, default: () => [] },
    brandSuffix: { type: String, default: "" },
    homeHref: { type: String, default: "/" },
    maxWidth: { type: String, default: "max-w-[1480px]" },
});

const { isDark, toggleTheme } = useTheme();
const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}

const mobileNavContainer = ref(null);

const scrollToActiveTab = async () => {
    await nextTick();     
    if (!mobileNavContainer.value) return; 
    const activeEl = mobileNavContainer.value.querySelector('.tag-primary');
    if (activeEl) {
        activeEl.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest', 
            inline: 'center' 
        });
    }
};

onMounted(() => {
    scrollToActiveTab();
});

watch(() => page.url, () => {
    scrollToActiveTab();
});
</script>

<template>
    <div class="relative min-h-screen overflow-x-clip bg-[var(--background)] text-[var(--foreground)]">
        <AppAtmosphere />

        <!-- Top navigation -->
        <header class="sticky top-0 z-30">
            <div class="mx-auto px-5 pt-4 lg:px-8" :class="maxWidth">
                <nav
                    class="nav-glass flex items-center justify-between gap-4 px-4 py-2.5 pl-5"
                    aria-label="Navigasi utama"
                >
                    <div class="flex items-center gap-6">
                        <Link :href="homeHref" class="group flex items-center gap-2.5">
                            <span class="brand-mark">
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.4"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M4 16 L9 7 L13 12 L20 4" />
                                </svg>
                            </span>
                            <span class="display text-[17px] tracking-tight text-[var(--ink)]">
                                Trimexas
                                <span v-if="brandSuffix" class="text-[13px] font-normal text-[var(--muted)]">
                                    / {{ brandSuffix }}
                                </span>
                            </span>
                        </Link>

                        <div class="hidden items-center gap-6 text-sm font-medium lg:flex">
                            <Link
                                v-for="item in nav"
                                :key="item.label"
                                :href="item.route"
                                class="nav-link"
                                :class="{ 'text-[var(--ink)]': item.active }"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <BellDropdown />
                        <button
                            type="button"
                            class="grid h-9 w-9 place-items-center rounded-full border border-[var(--border)] bg-[var(--surface)] text-[var(--muted)] transition-colors hover:border-[var(--primary)] hover:text-[var(--primary)]"
                            :aria-label="isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap'"
                            @click="toggleTheme"
                        >
                            <SunMedium v-if="isDark" :size="15" class="theme-icon is-dark" />
                            <MoonStar v-else :size="15" class="theme-icon" />
                        </button>
                        <span v-if="user" class="hidden text-sm text-[var(--muted)] sm:inline">
                            {{ user.name }}
                        </span>
                        <button
                            type="button"
                            class="btn-ghost !px-3.5 !py-2 text-[0.82rem]"
                            @click="logout"
                        >
                            <LogOut :size="14" />
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </div>
                </nav>

                <!-- Mobile nav row -->
                <div
                    ref="mobileNavContainer"
                    v-if="nav.length"
                    class="mt-2 flex gap-2 overflow-x-auto px-1 pb-1 lg:hidden"
                >
                    <Link
                        v-for="item in nav"
                        :key="`m-${item.label}`"
                        :href="item.route"
                        class="tag whitespace-nowrap"
                        :class="item.active ? 'tag-primary' : ''"
                    >
                        {{ item.label }}
                    </Link>
                </div>
            </div>
        </header>

        <main class="relative z-10 mx-auto px-5 py-10 lg:px-8" :class="maxWidth">
            <slot />
        </main>
    </div>
</template>
