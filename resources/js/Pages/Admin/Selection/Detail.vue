<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import Button from "@/components/ui/Button.vue";
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
            // refresh page to load full ranking
            router.reload({ preserveScroll: true });
        }
    } catch (_) {
        // ignore — try again next tick
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

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head :title="`Batch ${batch.label}`" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10">
            <div class="flex items-baseline justify-between">
                <div>
                    <h1 class="font-display text-3xl font-semibold tracking-tight">
                        {{ batch.label }}
                    </h1>
                    <p class="mt-1 text-sm text-[var(--muted)]">
                        Status: <span class="font-medium uppercase">{{ status }}</span>
                        · {{ processed }} / {{ total }} kandidat
                    </p>
                </div>
                <Link
                    v-if="!isRunning"
                    :href="route('admin.selection.audit', { batch: batch.id, candidate: results[0]?.student_id ?? 0 })"
                    class="text-sm text-[var(--primary)] hover:underline"
                >
                    Audit kandidat teratas →
                </Link>
            </div>

            <Card v-if="isRunning" variant="elevated" class="mt-6 p-6">
                <p class="text-sm font-medium">Memproses kandidat…</p>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-[var(--border)]">
                    <div
                        class="h-2 rounded-full bg-[var(--primary)] transition-all"
                        :style="{ width: `${percentage}%` }"
                    ></div>
                </div>
                <p class="mt-2 text-xs text-[var(--muted)]">{{ percentage }}%</p>
            </Card>

            <Card v-if="errorSummary" variant="outline" class="mt-4 p-4 border-red-300/40 bg-red-50/40">
                <p class="font-medium text-red-700">Batch gagal</p>
                <pre class="mt-2 text-xs text-red-700">{{ errorSummary }}</pre>
            </Card>

            <Card variant="elevated" class="mt-6 overflow-hidden">
                <div class="border-b border-[var(--border)] bg-[var(--primary-soft)]/40 px-4 py-2 text-xs uppercase tracking-wide text-[var(--muted)]">
                    Ranking ({{ results.length }} kandidat eligible)
                </div>
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
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
                        <tr v-for="r in results" :key="r.student_id">
                            <td class="px-4 py-3 font-medium">{{ r.rank }}</td>
                            <td class="px-4 py-3">{{ r.name }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ r.nim }}</td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ Number(r.score).toFixed(2) }}
                            </td>
                            <td class="px-4 py-3 capitalize">{{ r.category }}</td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="route('admin.selection.audit', { batch: batch.id, candidate: r.student_id })"
                                    class="text-[var(--primary)] hover:underline"
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
            </Card>

            <Card v-if="ineligible.length" variant="outline" class="mt-6 overflow-hidden">
                <div class="border-b border-[var(--border)] bg-[#FEF3C7]/40 px-4 py-2 text-xs uppercase tracking-wide text-[var(--muted)]">
                    Tidak Memenuhi Syarat ({{ ineligible.length }})
                </div>
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="row in ineligible" :key="row.student_id">
                            <td class="px-4 py-3">{{ row.name }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ row.nim }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span v-for="reason in row.reasons" :key="reason" class="mr-1 rounded bg-[var(--primary-soft)] px-2 py-0.5">
                                    {{ reason }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Card>
        </main>
    </div>
</template>
