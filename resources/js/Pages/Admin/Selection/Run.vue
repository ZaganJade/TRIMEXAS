<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";

defineProps({
    lastBatches: { type: Array, default: () => [] },
});

const form = useForm({ label: "" });

function submit() {
    form.post(route("admin.selection.run"));
}

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Run Selection" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6 py-10">
            <h1 class="font-display text-3xl font-semibold tracking-tight">Jalankan Seleksi</h1>
            <p class="mt-2 text-sm text-[var(--muted)]">
                Snapshot parameter himpunan, rules, dan thresholds akan dibekukan saat batch dimulai.
            </p>

            <Card variant="elevated" class="mt-8 p-6">
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="space-y-1.5">
                        <Label for="label" required>Label batch</Label>
                        <Input
                            id="label"
                            v-model="form.label"
                            placeholder="contoh: Semester Genap 2026"
                            :invalid="!!form.errors.label"
                        />
                        <p v-if="form.errors.label" class="text-xs text-red-600">
                            {{ form.errors.label }}
                        </p>
                    </div>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? "Memproses…" : "Run Selection" }}
                    </Button>
                </form>
            </Card>

            <Card v-if="lastBatches.length" variant="outline" class="mt-6 overflow-hidden">
                <div class="border-b border-[var(--border)] bg-[var(--primary-soft)]/30 px-4 py-2 text-xs uppercase tracking-wide text-[var(--muted)]">
                    Riwayat batch terbaru
                </div>
                <ul class="divide-y divide-[var(--border)]">
                    <li
                        v-for="b in lastBatches"
                        :key="b.id"
                        class="flex items-center justify-between px-4 py-3 text-sm"
                    >
                        <div>
                            <p class="font-medium">{{ b.label }}</p>
                            <p class="text-xs text-[var(--muted)]">
                                {{ new Date(b.created_at).toLocaleString("id-ID") }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs uppercase text-[var(--muted)]">{{ b.status }}</span>
                            <Link
                                :href="route('admin.selection.show', b.id)"
                                class="text-sm text-[var(--primary)] hover:underline"
                            >
                                Lihat
                            </Link>
                        </div>
                    </li>
                </ul>
            </Card>
        </main>
    </div>
</template>
