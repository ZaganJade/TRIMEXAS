<script setup>
/**
 * Mahasiswa layout — wraps pages in the shared shell with the student nav set.
 * Pass `active` (route key) to highlight the current item.
 */
import { computed } from "vue";
import AppShell from "@/components/AppShell.vue";

const props = defineProps({
    active: { type: String, default: "" },
});

const nav = computed(() => {
    const items = [
        { label: "Dashboard", route: route("mahasiswa.dashboard"), key: "dashboard" },
        { label: "Profil", route: route("mahasiswa.profile.show"), key: "profile" },
        { label: "Prestasi", route: route("mahasiswa.achievements.index"), key: "achievements" },
        { label: "Ranking", route: route("mahasiswa.ranking.index"), key: "ranking" },
    ];
    return items.map((i) => ({ ...i, active: i.key === props.active }));
});
</script>

<template>
    <AppShell
        brand-suffix="Mahasiswa"
        :home-href="route('mahasiswa.dashboard')"
        :nav="nav"
        max-width="max-w-7xl"
    >
        <slot />
    </AppShell>
</template>
