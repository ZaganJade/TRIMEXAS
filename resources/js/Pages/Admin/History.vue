<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";

const props = defineProps({
    batches: { type: Object, required: true },
    status: { type: String, default: null },
});

const filter = ref(props.status ?? "");

function applyFilter() {
    router.get(route("admin.history.index"), filter.value ? { status: filter.value } : {}, {
        preserveState: true,
    });
}

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Riwayat Batch" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10 space-y-4">
            <div class="flex items-baseline justify-between">
                <h1 class="font-display text-3xl font-semibold tracking-tight">Riwayat Batch Seleksi</h1>
                <Link :href="route('admin.selection.run')" class="text-sm text-[var(--primary)] hover:underline">+ Batch baru</Link>
            </div>

            <div class="flex gap-2">
                <select v-model="filter" class="h-10 rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm">
                    <option value="">Semua status</option>
                    <option value="queued">Queued</option>
                    <option value="running">Running</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
                <Button variant="secondary" size="sm" @click="applyFilter">Filter</Button>
            </div>

            <Card variant="elevated" class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">Label</th>
                            <th class="px-4 py-3">Dijalankan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Eligible</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="b in batches.data" :key="b.id">
                            <td class="px-4 py-3 font-medium">{{ b.label }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ b.triggered_by ?? '—' }}</td>
                            <td class="px-4 py-3 uppercase text-xs">{{ b.status }}</td>
                            <td class="px-4 py-3">{{ b.total_candidates }}</td>
                            <td class="px-4 py-3">{{ b.total_eligible }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">
                                {{ new Date(b.created_at).toLocaleString("id-ID") }}
                            </td>
                            <td class="px-4 py-3">
                                <Link :href="route('admin.selection.show', b.id)" class="text-[var(--primary)] hover:underline">Lihat</Link>
                            </td>
                        </tr>
                        <tr v-if="!batches.data.length">
                            <td colspan="7" class="px-4 py-10 text-center text-[var(--muted)]">Belum ada batch.</td>
                        </tr>
                    </tbody>
                </table>
            </Card>
        </main>
    </div>
</template>
