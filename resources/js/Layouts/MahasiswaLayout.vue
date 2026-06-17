<script setup>
/**
 * Mahasiswa layout — same sidebar shell as admin, with student navigation.
 */
import { computed } from "vue";
import AppSidebarLayout from "@/components/Layout/AppSidebarLayout.vue";
import {
    Home,
    User,
    Trophy,
    LineChart,
    ListOrdered,
    UserPen,
    PlusCircle,
} from "@lucide/vue";

defineProps({
    active: { type: String, default: "" },
});

const nav = computed(() => [
    {
        label: "Dashboard",
        route: route("mahasiswa.dashboard"),
        key: "dashboard",
        icon: Home,
    },
    {
        label: "Profil",
        route: route("mahasiswa.profile.show"),
        key: "profile",
        icon: User,
    },
    {
        label: "Prestasi",
        route: route("mahasiswa.achievements.index"),
        key: "achievements",
        icon: Trophy,
    },
    {
        label: "Hasil Analisa",
        route: route("mahasiswa.analysis.index"),
        key: "analysis",
        icon: LineChart,
    },
    {
        label: "Ranking",
        route: route("mahasiswa.ranking.index"),
        key: "ranking",
        icon: ListOrdered,
    },
]);

const quickAccess = computed(() => [
    {
        label: "Edit Profil",
        route: route("mahasiswa.profile.show"),
        icon: UserPen,
    },
    {
        label: "Tambah Prestasi",
        route: route("mahasiswa.achievements.index"),
        icon: PlusCircle,
    },
    {
        label: "Lihat Analisa",
        route: route("mahasiswa.analysis.index"),
        icon: LineChart,
    },
]);
</script>

<template>
    <AppSidebarLayout
        :active="active"
        :nav="nav"
        :quick-access="quickAccess"
        :home-href="route('mahasiswa.dashboard')"
        brand-suffix="Mahasiswa"
        role-label="Mahasiswa"
    >
        <slot />
    </AppSidebarLayout>
</template>
