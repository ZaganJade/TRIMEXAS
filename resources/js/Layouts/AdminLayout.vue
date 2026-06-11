<script setup>
/**
 * Admin layout — hover-based sidebar navigation with auto-expand
 */
import { computed, ref, onMounted, onBeforeUnmount } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import AppAtmosphere from "@/components/AppAtmosphere.vue";
import BellDropdown from "@/components/Notifications/BellDropdown.vue";
import { useTheme } from "@/composables/useTheme";
import {
    Home,
    Users,
    UserCheck,
    SlidersHorizontal,
    PlayCircle,
    History,
    ScrollText,
    LogOut,
    SunMedium,
    MoonStar,
    Menu,
    X,
    Bell,
} from "@lucide/vue";

const props = defineProps({
    active: { type: String, default: "" },
});

const { isDark, toggleTheme } = useTheme();
const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

// Sidebar state - now hover-based, starts collapsed on desktop
const isSidebarOpen = ref(false);
const isMobileMenuOpen = ref(false);
const hoverZone = ref(false);
const sidebarTimer = ref(null);
const HOVER_DELAY = 150; // ms delay before expanding
const HOVER_ZONE_WIDTH = 48; // px - detection zone on left edge

// Navigation items
const nav = computed(() => [
    {
        label: "Dashboard",
        route: route("admin.dashboard"),
        key: "dashboard",
        icon: Home,
    },
    {
        label: "Mahasiswa",
        route: route("admin.students.index"),
        key: "students",
        icon: Users,
    },
    {
        label: "Verifikasi",
        route: route("admin.students.pending"),
        key: "pending",
        icon: UserCheck,
        badge: page.props.stats?.pending || null,
    },
    {
        label: "Kriteria",
        route: route("admin.criteria.index"),
        key: "criteria",
        icon: SlidersHorizontal,
    },
    {
        label: "Seleksi",
        route: route("admin.selection.run"),
        key: "selection",
        icon: PlayCircle,
    },
    {
        label: "Riwayat",
        route: route("admin.history.index"),
        key: "history",
        icon: History,
    },
    {
        label: "Log Aktivitas",
        route: route("admin.activity.index"),
        key: "activity",
        icon: ScrollText,
    },
]);

// Quick access items
const quickAccess = computed(() => [
    {
        label: "Tambah Mahasiswa",
        route: route("admin.students.create"),
        icon: UserCheck,
    },
    {
        label: "Jalankan Seleksi",
        route: route("admin.selection.run"),
        icon: PlayCircle,
    },
    {
        label: "Atur Kriteria",
        route: route("admin.criteria.index"),
        icon: SlidersHorizontal,
    },
]);

// Mobile menu functions
const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

// Hover-based sidebar expansion
const onMouseEnterSidebar = () => {
    hoverZone.value = true;
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
        sidebarTimer.value = null;
    }
    expandSidebar();
};

const onMouseLeaveSidebar = () => {
    hoverZone.value = false;
    // Delay collapse to prevent flickering
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
    }
    sidebarTimer.value = setTimeout(() => {
        if (!hoverZone.value) {
            collapseSidebar();
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
            expandSidebar();
        }
    }, HOVER_DELAY);
};

const onMouseLeaveHoverZone = () => {
    hoverZone.value = false;
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
    }
};

const expandSidebar = () => {
    isSidebarOpen.value = true;
};

const collapseSidebar = () => {
    isSidebarOpen.value = false;
};

// Logout
const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}

// Get greeting based on time
const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return "Selamat pagi";
    if (hour < 15) return "Selamat siang";
    if (hour < 18) return "Selamat sore";
    return "Selamat malam";
};

// Cleanup timer on unmount
onBeforeUnmount(() => {
    if (sidebarTimer.value) {
        clearTimeout(sidebarTimer.value);
    }
});
</script>

<template>
    <div class="admin-layout">
        <AppAtmosphere />

        <!-- Hover Detection Zone (invisible strip on left edge) -->
        <div
            v-if="!isMobileMenuOpen"
            class="hover-zone"
            @mouseenter="onMouseEnterHoverZone"
            @mouseleave="onMouseLeaveHoverZone"
        ></div>

        <!-- Mobile Overlay -->
        <Teleport to="body">
            <div
                v-if="isMobileMenuOpen"
                class="mobile-overlay"
                @click="closeMobileMenu"
            ></div>
        </Teleport>

        <!-- Sidebar -->
        <aside
            class="admin-sidebar"
            :class="{
                'sidebar-expanded': isSidebarOpen,
                'sidebar-mobile-open': isMobileMenuOpen,
            }"
            @mouseenter="onMouseEnterSidebar"
            @mouseleave="onMouseLeaveSidebar"
        >
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <Link :href="route('admin.dashboard')" class="sidebar-brand">
                    <span class="brand-icon">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4">
                            <path d="M4 16 L9 7 L13 12 L20 4" />
                        </svg>
                    </span>
                    <Transition name="brand-text">
                        <span v-show="isSidebarOpen" class="sidebar-brand-text">Trimexas</span>
                    </Transition>
                </Link>

                <!-- Mobile Close -->
                <button
                    class="mobile-close-btn mobile-only"
                    @click="closeMobileMenu"
                >
                    <X :size="20" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav" aria-label="Navigasi admin">
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

                <!-- Quick Access -->
                <Transition name="quick-access">
                    <div v-if="isSidebarOpen" class="nav-section">
                        <p class="nav-section-title">Quick Access</p>
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

            <!-- Sidebar Footer -->
            <Transition name="sidebar-footer">
                <div v-if="isSidebarOpen" class="sidebar-footer">
                    <div class="sidebar-user">
                        <div class="user-avatar">
                            {{ user?.name?.charAt(0) || "A" }}
                        </div>
                        <div class="user-info">
                            <p class="user-name">{{ user?.name || "Admin" }}</p>
                            <p class="user-role">Pengelola</p>
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
                        <button
                            class="sidebar-action-btn"
                            title="Keluar"
                            @click="logout"
                        >
                            <LogOut :size="16" />
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Collapsed Footer Icons -->
            <div v-if="!isSidebarOpen" class="sidebar-footer-collapsed">
                <button
                    class="footer-icon-btn"
                    @click="toggleTheme"
                    :title="isDark ? 'Tema terang' : 'Tema gelap'"
                >
                    <SunMedium v-if="isDark" :size="18" />
                    <MoonStar v-else :size="18" />
                </button>
                <button
                    class="footer-icon-btn"
                    title="Keluar"
                    @click="logout"
                >
                    <LogOut :size="18" />
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main 
        class="admin-main"
        :class="{ 'main-sidebar-expanded': isSidebarOpen && !isMobileMenuOpen }" 
        >
            <!-- Top Header -->
            <header class="admin-header">
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Toggle -->
                    <button
                        class="mobile-menu-btn"
                        @click="toggleMobileMenu"
                    >
                        <Menu :size="22" />
                    </button>

                    <!-- Page Title -->
                    <div class="header-title desktop-only">
                        <h1 class="header-name">{{ user?.name || "Admin" }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <BellDropdown />

                    <!-- Desktop User Info -->
                    <div class="header-user desktop-only">
                        <div class="header-user-avatar">
                            {{ user?.name?.charAt(0) || "A" }}
                        </div>
                        <div class="header-user-info">
                            <p class="header-user-name">{{ user?.name || "Admin" }}</p>
                            <p class="header-user-role">Pengelola</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="admin-content">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
/* =====================================================
   Admin Layout Structure
   ===================================================== */
.admin-layout {
    display: flex;
    min-height: 100vh;
    background: var(--background);
}

/* =====================================================
   Hover Detection Zone
   ===================================================== */
.hover-zone {
    position: fixed;
    top: 0;
    left: 0;
    width: 80px;
    height: 100vh;
    z-index: 49;
    pointer-events: auto;
    /* Invisible but detects hover - wider than collapsed sidebar for smoother interaction */
}

@media (max-width: 1024px) {
    .hover-zone {
        display: none;
    }
}

/* =====================================================
   Sidebar
   ===================================================== */
.admin-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 72px;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    z-index: 50;
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-sidebar.sidebar-expanded {
    width: 260px;
}

/* Mobile Sidebar */
@media (max-width: 1024px) {
    .admin-sidebar {
        transform: translateX(-100%);
        width: 280px;
        transition: transform 0.3s ease;
    }

    .admin-sidebar.sidebar-mobile-open {
        transform: translateX(0);
    }

    .admin-sidebar.sidebar-expanded {
        width: 280px;
    }
}

/* Mobile Overlay */
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

/* =====================================================
   Sidebar Header
   ===================================================== */
.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
    position: relative;
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
    flex-shrink: 0;
}

/* Transitions for sidebar elements */
.brand-text-enter-active,
.brand-text-leave-active {
    transition: all 0.2s ease;
}

.brand-text-enter-from,
.brand-text-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

.section-title-enter-active,
.section-title-leave-active {
    transition: all 0.2s ease;
}

.section-title-enter-from,
.section-title-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

.nav-label-enter-active,
.nav-label-leave-active {
    transition: all 0.2s ease;
}

.nav-label-enter-from,
.nav-label-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

.nav-badge-enter-active,
.nav-badge-leave-active {
    transition: all 0.2s ease;
}

.nav-badge-enter-from,
.nav-badge-leave-to {
    opacity: 0;
    transform: scale(0.8);
}

.quick-access-enter-active,
.quick-access-leave-active {
    transition: all 0.2s ease;
}

.quick-access-enter-from,
.quick-access-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.sidebar-footer-enter-active,
.sidebar-footer-leave-active {
    transition: all 0.2s ease;
}

.sidebar-footer-enter-from,
.sidebar-footer-leave-to {
    opacity: 0;
    transform: translateX(-10px);
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
    transition: all 0.2s;
}

.mobile-close-btn:hover {
    background: var(--border);
    color: var(--ink);
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

/* =====================================================
   Sidebar Navigation
   ===================================================== */
.sidebar-nav {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
    overflow-x: hidden;
}

.nav-section {
    margin-bottom: 1.5rem;
}

.nav-section:last-child {
    margin-bottom: 0;
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
    transition: all 0.2s;
    position: relative;
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
    flex-shrink: 0;
}

.nav-item-quick {
    font-size: 13px;
    padding: 0.6rem 0.75rem;
}

/* =====================================================
   Sidebar Footer
   ===================================================== */
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

.user-avatar {
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

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role {
    font-size: 12px;
    color: var(--muted);
}

.sidebar-actions {
    display: flex;
    gap: 0.5rem;
}

.sidebar-action-btn {
    flex: 1;
    height: 36px;
    display: grid;
    place-items: center;
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 8px;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
}

.sidebar-action-btn:hover {
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
    border: 1px solid var(--border);
    background: var(--surface-2);
    border-radius: 8px;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.2s;
}

.footer-icon-btn:hover {
    background: var(--border);
    color: var(--ink);
}

/* =====================================================
   Main Content
   ===================================================== */
.admin-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 72px;
    transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-main.main-sidebar-expanded {
    margin-left: 260px; 
}

@media (max-width: 1024px) {
    .admin-main,
    .admin-main.main-sidebar-expanded {
        margin-left: 0;
    }
}

/* =====================================================
   Admin Header
   ===================================================== */
.admin-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 2rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
    position: sticky;
    top: 0;
    z-index: 30;
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
    transition: all 0.2s;
}

.mobile-menu-btn:hover {
    background: var(--border);
    color: var(--ink);
}

@media (max-width: 1024px) {
    .mobile-menu-btn {
        display: grid;
    }
}

.header-title {
    display: none;
}

@media (max-width: 1024px) {
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

.header-user-avatar {
    height: 38px;
    width: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
    display: grid;
    place-items: center;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.header-user-info {
    text-align: left;
}

.header-user-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--ink);
    line-height: 1.2;
}

.header-user-role {
    font-size: 12px;
    color: var(--muted);
    margin-top: 0.15rem;
}

/* =====================================================
   Admin Content Area
   ===================================================== */
.admin-content {
    flex: 1;
    padding: 2rem;
    overflow: auto;
}

@media (max-width: 768px) {
    .admin-content {
        padding: 1rem;
    }
}
</style>
