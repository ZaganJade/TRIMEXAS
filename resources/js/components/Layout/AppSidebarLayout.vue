<script setup>
/**
 * Shared sidebar shell — hover-expand sidebar, sticky header, notification bell.
 * Used by AdminLayout and MahasiswaLayout with different nav configurations.
 */
import { computed, ref, onBeforeUnmount } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import AppAtmosphere from "@/components/AppAtmosphere.vue";
import BellDropdown from "@/components/Notifications/BellDropdown.vue";
import { useTheme } from "@/composables/useTheme";
import {
    LogOut,
    SunMedium,
    MoonStar,
    Menu,
    X,
} from "@lucide/vue";

const props = defineProps({
    active: { type: String, default: "" },
    nav: { type: Array, required: true },
    quickAccess: { type: Array, default: () => [] },
    homeHref: { type: String, required: true },
    brandSuffix: { type: String, default: "" },
    roleLabel: { type: String, default: "Pengguna" },
});

const { isDark, toggleTheme } = useTheme();
const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const isSidebarOpen = ref(true);
const isMobileMenuOpen = ref(false);
const hoverZone = ref(false);
const sidebarTimer = ref(null);
const HOVER_DELAY = 150;

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

const onMouseEnterSidebar = () => {
    hoverZone.value = true;
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
        sidebarTimer.value = null;
    }
    isSidebarOpen.value = true;
};

const onMouseLeaveSidebar = () => {
    hoverZone.value = false;
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
    }
    sidebarTimer.value = setTimeout(() => {
        if (!hoverZone.value) {
            isSidebarOpen.value = false;
        }
    }, 300);
};

const onMouseEnterHoverZone = () => {
    hoverZone.value = true;
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
        sidebarTimer.value = null;
    }
    sidebarTimer.value = setTimeout(() => {
        if (hoverZone.value) {
            isSidebarOpen.value = true;
        }
    }, HOVER_DELAY);
};

const onMouseLeaveHoverZone = () => {
    hoverZone.value = false;
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
    }
};

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}

onBeforeUnmount(() => {
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
    }
});
</script>

<template>
    <div class="app-sidebar-layout">
        <AppAtmosphere />

        <div
            v-if="!isMobileMenuOpen"
            class="hover-zone"
            @mouseenter="onMouseEnterHoverZone"
            @mouseleave="onMouseLeaveHoverZone"
        ></div>

        <Teleport to="body">
            <div
                v-if="isMobileMenuOpen"
                class="mobile-overlay"
                @click="closeMobileMenu"
            ></div>
        </Teleport>

        <aside
            class="app-sidebar"
            :class="{
                'sidebar-collapsed': !isSidebarOpen,
                'sidebar-mobile-open': isMobileMenuOpen,
            }"
            @mouseenter="onMouseEnterSidebar"
            @mouseleave="onMouseLeaveSidebar"
        >
            <div class="sidebar-header">
                <Link :href="homeHref" class="sidebar-brand">
                    <span class="brand-icon">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4">
                            <path d="M4 16 L9 7 L13 12 L20 4" />
                        </svg>
                    </span>
                    <Transition name="brand-text">
                        <span v-show="isSidebarOpen" class="sidebar-brand-text">
                            Trimexas
                            <span v-if="brandSuffix" class="sidebar-brand-suffix">/ {{ brandSuffix }}</span>
                        </span>
                    </Transition>
                </Link>

                <button class="mobile-close-btn mobile-only" @click="closeMobileMenu">
                    <X :size="20" />
                </button>
            </div>

            <nav class="sidebar-nav" aria-label="Navigasi utama">
                <div class="nav-section">
                    <Transition name="section-title">
                        <p v-show="isSidebarOpen" class="nav-section-title">Menu Utama</p>
                    </Transition>
                    <div class="nav-items">
                        <Link
                            v-for="item in nav"
                            :key="item.key"
                            :href="item.route"
                            class="nav-item"
                            :class="{ 'nav-item-active': item.key === active }"
                            :title="!isSidebarOpen ? item.label : ''"
                            @click="closeMobileMenu"
                        >
                            <component :is="item.icon" :size="20" class="nav-icon" />
                            <Transition name="nav-label">
                                <span v-show="isSidebarOpen" class="nav-label">{{ item.label }}</span>
                            </Transition>
                            <Transition name="nav-badge">
                                <span v-if="item.badge && isSidebarOpen" class="nav-badge">{{ item.badge }}</span>
                            </Transition>
                        </Link>
                    </div>
                </div>

                <Transition name="quick-access">
                    <div v-if="isSidebarOpen && quickAccess.length" class="nav-section">
                        <p class="nav-section-title">Pintasan</p>
                        <div class="nav-items">
                            <Link
                                v-for="item in quickAccess"
                                :key="item.label"
                                :href="item.route"
                                class="nav-item nav-item-quick"
                                @click="closeMobileMenu"
                            >
                                <component :is="item.icon" :size="18" class="nav-icon" />
                                <span class="nav-label">{{ item.label }}</span>
                            </Link>
                        </div>
                    </div>
                </Transition>
            </nav>

            <Transition name="sidebar-footer">
                <div v-if="isSidebarOpen" class="sidebar-footer">
                    <div class="sidebar-user">
                        <div class="user-avatar">
                            {{ user?.name?.charAt(0) || "U" }}
                        </div>
                        <div class="user-info">
                            <p class="user-name">{{ user?.name || "Pengguna" }}</p>
                            <p class="user-role">{{ roleLabel }}</p>
                        </div>
                    </div>

                    <div class="sidebar-actions">
                        <button
                            class="sidebar-action-btn"
                            @click="toggleTheme"
                            :title="isDark ? 'Tema terang' : 'Tema gelap'"
                        >
                            <SunMedium v-if="isDark" :size="16" />
                            <MoonStar v-else :size="16" />
                        </button>
                        <button class="sidebar-action-btn" title="Keluar" @click="logout">
                            <LogOut :size="16" />
                        </button>
                    </div>
                </div>
            </Transition>

            <div v-if="!isSidebarOpen" class="sidebar-footer-collapsed">
                <button
                    class="footer-icon-btn"
                    @click="toggleTheme"
                    :title="isDark ? 'Tema terang' : 'Tema gelap'"
                >
                    <SunMedium v-if="isDark" :size="18" />
                    <MoonStar v-else :size="18" />
                </button>
                <button class="footer-icon-btn" title="Keluar" @click="logout">
                    <LogOut :size="18" />
                </button>
            </div>
        </aside>

        <main class="app-main" :class="{ 'main-sidebar-collapsed': !isSidebarOpen && !isMobileMenuOpen }">
            <header class="app-header">
                <div class="flex items-center gap-4">
                    <button class="mobile-menu-btn" @click="toggleMobileMenu">
                        <Menu :size="22" />
                    </button>

                    <div class="header-title desktop-only">
                        <h1 class="header-name">{{ user?.name || roleLabel }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <BellDropdown />

                    <div class="header-user desktop-only">
                        <div class="header-user-avatar">
                            {{ user?.name?.charAt(0) || "U" }}
                        </div>
                        <div class="header-user-info">
                            <p class="header-user-name">{{ user?.name || roleLabel }}</p>
                            <p class="header-user-role">{{ roleLabel }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <div class="app-content">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.app-sidebar-layout {
    display: flex;
    min-height: 100vh;
    background: var(--background);
}

.hover-zone {
    position: fixed;
    top: 0;
    left: 0;
    width: 80px;
    height: 100vh;
    z-index: 49;
    pointer-events: auto;
}

@media (max-width: 1024px) {
    .hover-zone {
        display: none;
    }
}

.app-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    z-index: 50;
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.app-sidebar.sidebar-collapsed {
    width: 72px;
}

@media (max-width: 1024px) {
    .app-sidebar {
        transform: translateX(-100%);
        width: 280px;
        transition: transform 0.3s ease;
    }

    .app-sidebar.sidebar-mobile-open {
        transform: translateX(0);
    }

    .app-sidebar.sidebar-collapsed {
        width: 280px;
    }
}

.mobile-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 40;
    animation: fade-in 0.2s ease;
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    flex: 1;
    min-width: 0;
}

.brand-icon {
    display: grid;
    height: 36px;
    width: 36px;
    place-items: center;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
    border-radius: 10px;
    flex-shrink: 0;
}

.sidebar-brand-text {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    color: var(--ink);
    white-space: nowrap;
}

.sidebar-brand-suffix {
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
}

.brand-text-enter-active,
.brand-text-leave-active,
.section-title-enter-active,
.section-title-leave-active,
.nav-label-enter-active,
.nav-label-leave-active,
.nav-badge-enter-active,
.nav-badge-leave-active,
.quick-access-enter-active,
.quick-access-leave-active,
.sidebar-footer-enter-active,
.sidebar-footer-leave-active {
    transition: all 0.2s ease;
}

.brand-text-enter-from,
.brand-text-leave-to,
.section-title-enter-from,
.section-title-leave-to,
.nav-label-enter-from,
.nav-label-leave-to,
.sidebar-footer-enter-from,
.sidebar-footer-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

.nav-badge-enter-from,
.nav-badge-leave-to {
    opacity: 0;
    transform: scale(0.8);
}

.quick-access-enter-from,
.quick-access-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.mobile-close-btn {
    display: none;
    height: 32px;
    width: 32px;
    place-items: center;
    border: none;
    background: var(--surface-2);
    border-radius: 8px;
    color: var(--muted);
    cursor: pointer;
}

.mobile-only {
    display: none;
}

@media (max-width: 1024px) {
    .desktop-only {
        display: none !important;
    }

    .mobile-only {
        display: grid !important;
    }
}

.sidebar-nav {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    overflow-x: hidden;
}

.nav-section {
    margin-bottom: 1.5rem;
}

.nav-section-title {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--muted);
    margin-bottom: 0.75rem;
    padding-left: 0.75rem;
    white-space: nowrap;
}

.nav-items {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 10px;
    text-decoration: none;
    color: var(--muted);
    transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
}

.nav-item:hover {
    background: var(--surface-2);
    color: var(--ink);
}

.nav-item-active {
    background: var(--primary-light);
    color: var(--primary);
    font-weight: 500;
}

.nav-icon {
    flex-shrink: 0;
}

.nav-label {
    flex: 1;
    font-size: 14px;
    white-space: nowrap;
}

.nav-badge {
    display: inline-flex;
    min-height: 20px;
    padding: 0 6px;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    background: var(--danger);
    color: white;
    border-radius: 10px;
}

.nav-item-quick {
    font-size: 13px;
    padding: 0.6rem 0.75rem;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid var(--border);
}

.sidebar-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.user-avatar,
.header-user-avatar {
    height: 40px;
    width: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
    display: grid;
    place-items: center;
    font-weight: 600;
    font-size: 15px;
    flex-shrink: 0;
}

.header-user-avatar {
    height: 38px;
    width: 38px;
    font-size: 14px;
}

.user-name,
.header-user-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role,
.header-user-role {
    font-size: 12px;
    color: var(--muted);
}

.sidebar-actions {
    display: flex;
    gap: 0.5rem;
}

.sidebar-action-btn,
.footer-icon-btn {
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 8px;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
}

.sidebar-action-btn {
    flex: 1;
    height: 36px;
    display: grid;
    place-items: center;
}

.sidebar-action-btn:hover,
.footer-icon-btn:hover {
    background: var(--border);
    color: var(--ink);
}

.sidebar-footer-collapsed {
    padding: 1rem;
    border-top: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.footer-icon-btn {
    height: 40px;
    display: grid;
    place-items: center;
}

.app-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 260px;
    transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    min-width: 0;
    /* Batasi tinggi ke viewport agar halaman TIDAK pernah scroll di
       level body/window. Body yang tidak pernah scroll membuat
       `body { overflow: hidden }` dari Radix (dropdown/dialog) menjadi
       no-op — sehingga sticky header tidak kehilangan anchor-nya dan
       tidak terjadi reflow scrollbar. */
    height: 100vh;
    height: 100dvh;
    overflow: hidden;
}

.app-main.main-sidebar-collapsed {
    margin-left: 72px;
}

@media (max-width: 1024px) {
    .app-main,
    .app-main.main-sidebar-collapsed {
        margin-left: 0;
    }
}

.app-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 2rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
    position: sticky;
    top: 0;
    z-index: 30;
    flex: 0 0 auto;
}

.mobile-menu-btn {
    display: none;
    height: 40px;
    width: 40px;
    place-items: center;
    border: none;
    background: var(--surface-2);
    border-radius: 10px;
    color: var(--muted);
    cursor: pointer;
}

@media (max-width: 1024px) {
    .mobile-menu-btn {
        display: grid;
    }

    .header-title {
        display: block;
    }

    .header-name {
        font-size: 18px;
        font-weight: 600;
        color: var(--ink);
    }
}

.header-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.app-content {
    flex: 1;
    padding: 2rem;
    overflow: auto;
    width: 100%;
    max-width: 1640px;
    margin: 0 auto;
    /* min-height:0 wajib agar flex-child ini bisa di-scroll di dalam
       .app-main yang tingginya dibatasi 100vh. scrollbar-gutter
       stable both-edges mencadangkan ruang scrollbar di kiri-kanan
       supaya konten yang di-center (margin:auto) tidak melompat saat
       overlay/dropdown dibuka. */
    min-height: 0;
    scrollbar-gutter: stable both-edges;
}

@media (max-width: 768px) {
    .app-content {
        padding: 1rem;
    }
}
</style>
