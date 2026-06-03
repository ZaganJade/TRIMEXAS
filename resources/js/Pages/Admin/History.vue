<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";

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
</script>

<template>
    <Head title="Riwayat Batch" />
    <AdminLayout active="history">
        <div class="window">
            <div class="window-bar">
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-title">Riwayat Batch Seleksi</span>
            </div>
            <div class="window-body flex items-center justify-between">
                <div class="flex gap-2">
                    <select v-model="filter" class="h-9 rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm mono">
                        <option value="">Semua status</option>
                        <option value="queued">Queued</option>
                        <option value="running">Running</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                    <Button variant="secondary" size="sm" @click="applyFilter">Filter</Button>
                </div>
                <Link :href="route('admin.selection.run')" class="btn-primary text-sm">+ Batch baru</Link>
            </div>
        </div>

        <div class="window mt-6">
            <div class="window-body p-0 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)] bg-[var(--surface)]">
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
                            <td class="px-4 py-3">
                                <span class="tag tag-primary mono uppercase text-xs">{{ b.status }}</span>
                            </td>
                            <td class="px-4 py-3 mono tnum">{{ b.total_candidates }}</td>
                            <td class="px-4 py-3 mono tnum">{{ b.total_eligible }}</td>
                            <td class="px-4 py-3 text-[var(--muted)] text-xs">
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
            </div>
        </div>
    </AdminLayout>
</template>
