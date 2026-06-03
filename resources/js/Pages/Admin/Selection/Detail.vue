<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Card from "@/components/ui/Card.vue";

const props = defineProps({
    batch: { type: Object, required: true },
    results: { type: Array, default: () => [] },
    ineligible: { type: Array, default: () => [] },
});

const status = ref(props.batch.status);
const total = ref(props.batch.total_candidates ?? 0);
const processed = ref(props.batch.processed_count ?? 0);
const errorSummary = ref(null);

const percentage = computed(() => (total.value > 0 ? Math.round((processed.value / total.value) * 100) : 0));
const isRunning = computed(() => status.value === "queued" || status.value === "running");

let pollHandle = null;

async function poll() {
    try {
        const res = await fetch(route("admin.selection.progress", props.batch.id), {
            headers: { Accept: "application/json" },
        });
        const json = await res.json();
        status.value = json.status;
        total.value = json.total;
        processed.value = json.processed;
        errorSummary.value = json.error_summary;

        if (json.status === "completed" || json.status === "failed") {
            stopPoll();
            router.reload({ preserveScroll: true });
        }
    } catch (_) {
        // ignore
    }
}

function startPoll() {
    if (pollHandle) return;
    pollHandle = setInterval(poll, 2000);
}

function stopPoll() {
    if (pollHandle) {
        clearInterval(pollHandle);
        pollHandle = null;
    }
}

onMounted(() => {
    if (isRunning.value) startPoll();
});

onBeforeUnmount(stopPoll);
</script>

<template>
    <Head :title="`Batch ${batch.label}`" />
    <AdminLayout active="selection">
        <div class="window">
            <div class="window-bar">
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-title">{{ batch.label }}</span>
            </div>
            <div class="window-body flex items-baseline justify-between">
                <p class="text-sm text-[var(--muted)]">
                    Status: <span class="tag tag-primary mono uppercase">{{ status }}</span>
                    · <span class="mono tnum">{{ processed }} / {{ total }}</span> kandidat
                </p>
                <Link
                    v-if="!isRunning && results.length"
                    :href="route('admin.selection.audit', { batch: batch.id, candidate: results[0]?.student_id ?? 0 })"
                    class="text-sm text-[var(--primary)] hover:underline"
                >
                    Audit kandidat teratas →
                </Link>
            </div>
        </div>

        <Card v-if="isRunning" variant="elevated" class="mt-6 p-6">
            <p class="text-sm font-medium">Memproses kandidat…</p>
            <div class="meter mt-3">
                <i :style="{ width: `${percentage}%` }"></i>
            </div>
            <p class="mt-2 text-xs text-[var(--muted)] mono">{{ percentage }}%</p>
        </Card>

        <Card v-if="errorSummary" variant="outline" class="mt-4 p-4 border-red-300/40 bg-red-50/40">
            <p class="font-medium text-red-700">Batch gagal</p>
            <pre class="mt-2 text-xs text-red-700 mono">{{ errorSummary }}</pre>
        </Card>

        <div class="window mt-6">
            <div class="window-bar">
                <span class="window-title text-xs">Ranking ({{ results.length }} kandidat eligible)</span>
            </div>
            <div class="window-body p-0 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)] bg-[var(--surface)]">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3 text-right">Skor</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="r in results" :key="r.student_id" class="rank-row">
                            <td class="px-4 py-3 rank-pos mono">{{ r.rank }}</td>
                            <td class="px-4 py-3 font-medium">{{ r.name }}</td>
                            <td class="px-4 py-3 text-[var(--muted)] mono">{{ r.nim }}</td>
                            <td class="px-4 py-3 text-right mono tnum">
                                {{ Number(r.score).toFixed(2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="tag tag-success capitalize">{{ r.category }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="route('admin.selection.audit', { batch: batch.id, candidate: r.student_id })"
                                    class="text-[var(--primary)] hover:underline text-sm"
                                >
                                    Audit
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!results.length">
                            <td colspan="6" class="px-4 py-10 text-center text-[var(--muted)]">
                                Belum ada hasil ranking.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="ineligible.length" class="window mt-6">
            <div class="window-bar bg-[var(--warning)]/20">
                <span class="window-title text-xs">Tidak Memenuhi Syarat ({{ ineligible.length }})</span>
            </div>
            <div class="window-body p-0 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)] bg-[var(--surface)]">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="row in ineligible" :key="row.student_id">
                            <td class="px-4 py-3 font-medium">{{ row.name }}</td>
                            <td class="px-4 py-3 text-[var(--muted)] mono">{{ row.nim }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span v-for="reason in row.reasons" :key="reason" class="tag tag-warning mr-1">
                                    {{ reason }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
