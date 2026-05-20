<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Admin · Dashboard" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link href="/" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-[var(--muted)]">{{ user?.name }}</span>
                    <Button variant="ghost" size="sm" @click="logout">Logout</Button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10">
            <h1 class="font-display text-3xl font-semibold tracking-tight">Dashboard Admin</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">
                Selamat datang. Modul lengkap akan ditambahkan pada milestone berikutnya.
            </p>

            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card variant="elevated" class="p-5">
                    <p class="text-xs text-[var(--muted)] uppercase tracking-wide">Menunggu Verifikasi</p>
                    <p class="mt-2 font-display text-3xl font-semibold">—</p>
                </Card>
                <Card variant="elevated" class="p-5">
                    <p class="text-xs text-[var(--muted)] uppercase tracking-wide">Mahasiswa Aktif</p>
                    <p class="mt-2 font-display text-3xl font-semibold">—</p>
                </Card>
                <Card variant="elevated" class="p-5">
                    <p class="text-xs text-[var(--muted)] uppercase tracking-wide">Batch Berjalan</p>
                    <p class="mt-2 font-display text-3xl font-semibold">—</p>
                </Card>
            </div>
        </main>
    </div>
</template>
