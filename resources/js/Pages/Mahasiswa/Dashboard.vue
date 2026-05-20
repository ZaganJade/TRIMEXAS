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
    <Head title="Mahasiswa · Dashboard" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link href="/" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Mahasiswa</span>
                </Link>
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-[var(--muted)]">{{ user?.name }}</span>
                    <Button variant="ghost" size="sm" @click="logout">Logout</Button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-6 py-10">
            <h1 class="font-display text-3xl font-semibold tracking-tight">
                Halo, {{ user?.name }}
            </h1>
            <p class="mt-2 text-sm text-[var(--muted)]">
                Profil dan ranking publik akan tersedia setelah modul mahasiswa diselesaikan.
            </p>

            <Card variant="elevated" class="mt-8 p-6">
                <p class="text-sm text-[var(--muted)]">
                    Status akun: <span class="font-medium text-emerald-600">Approved</span>
                </p>
            </Card>
        </main>
    </div>
</template>
