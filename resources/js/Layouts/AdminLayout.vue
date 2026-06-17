<script setup>
/**
 * Admin layout — sidebar navigation via shared AppSidebarLayout.
 */
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import AppSidebarLayout from "@/components/Layout/AppSidebarLayout.vue";
import {
    Home,
    Users,
    UserCheck,
    SlidersHorizontal,
    PlayCircle,
    History,
    ScrollText,
} from "@lucide/vue";

defineProps({
    active: { type: String, default: "" },
});

const page = usePage();

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
</script>

<template>
    <AppSidebarLayout
        :active="active"
        :nav="nav"
        :quick-access="quickAccess"
        :home-href="route('admin.dashboard')"
        role-label="Pengelola"
    >
        <slot />
    </AppSidebarLayout>
</template>
