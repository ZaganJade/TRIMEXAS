<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";

const props = defineProps({
    batch: { type: Object, required: true },
    candidate: { type: Object, required: true },
    result: { type: Object, required: true },
    evaluations: { type: Array, default: () => [] },
});

const showAll = ref(false);

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head :title="`Audit ${candidate.full_name}`" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin / Audit</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-10 space-y-6">
            <div class="flex items-baseline justify-between">
                <div>
                    <h1 class="font-display text-3xl font-semibold tracking-tight">
                        Audit · {{ candidate.full_name }}
                    </h1>
                    <p class="mt-1 text-sm text-[var(--muted)] font-mono">{{ candidate.nim }}</p>
                </div>
                <Link :href="route('admin.selection.show', batch.id)" class="text-sm text-[var(--primary)] hover:underline">
                    ← Kembali ke {{ batch.label }}
                </Link>
            </div>

            <Card v-if="!result.eligible" variant="outline" class="p-4 border-red-300/40 bg-red-50/40">
                <p class="font-medium text-red-700">Tidak Memenuhi Syarat</p>
                <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                    <li v-for="r in result.ineligibility_reasons" :key="r">{{ r }}</li>
                </ul>
            </Card>

            <Card v-else variant="elevated" class="p-6">
                <p class="text-xs uppercase tracking-wide text-[var(--muted)]">Skor akhir</p>
                <p class="mt-1 font-display text-4xl font-semibold">
                    {{ result.score?.toFixed?.(2) ?? result.score }}
                </p>
                <p class="mt-1 text-sm text-[var(--muted)] capitalize">
                    Kategori: <span class="font-medium">{{ result.status ?? result.category }}</span>
                    <span v-if="result.rank"> · Rank {{ result.rank }}</span>
                </p>
            </Card>

            <Card variant="elevated" class="p-6">
                <p class="font-medium">Input crisp</p>
                <pre class="mt-2 overflow-auto rounded-md bg-[var(--primary-soft)]/40 p-3 text-xs">{{ JSON.stringify(result.input_snapshot, null, 2) }}</pre>
            </Card>

            <Card variant="elevated" class="overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--border)] bg-[var(--primary-soft)]/40 px-4 py-2 text-xs uppercase tracking-wide text-[var(--muted)]">
                    <span>Rule yang dieksekusi</span>
                    <Button size="sm" variant="ghost" @click="showAll = !showAll">
                        {{ showAll ? "Sembunyikan α=0" : "Tampilkan semua rule" }}
                    </Button>
                </div>
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">Rule</th>
                            <th class="px-4 py-3">Consequent</th>
                            <th class="px-4 py-3 text-right">α</th>
                            <th class="px-4 py-3 text-right">z</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="e in (showAll ? evaluations : evaluations.filter((x) => Number(x.alpha) > 0))" :key="e.rule_code">
                            <td class="px-4 py-3 font-mono text-xs">{{ e.rule_code }}</td>
                            <td class="px-4 py-3 capitalize">{{ e.consequent }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ Number(e.alpha).toFixed(3) }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ Number(e.z).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </Card>
        </main>
    </div>
</template>
