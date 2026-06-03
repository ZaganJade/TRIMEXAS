<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";

const props = defineProps({
    batch: { type: Object, required: true },
    candidate: { type: Object, required: true },
    result: { type: Object, required: true },
    evaluations: { type: Array, default: () => [] },
});

const showAll = ref(false);
</script>

<template>
    <Head :title="`Audit ${candidate.full_name}`" />
    <AdminLayout active="selection">
        <div class="window">
            <div class="window-bar">
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-title">Audit · {{ candidate.full_name }}</span>
            </div>
            <div class="window-body flex items-baseline justify-between">
                <p class="mono text-sm text-[var(--muted)]">{{ candidate.nim }}</p>
                <Link :href="route('admin.selection.show', batch.id)" class="text-sm text-[var(--primary)] hover:underline">
                    ← Kembali ke {{ batch.label }}
                </Link>
            </div>
        </div>

        <Card v-if="!result.eligible" variant="outline" class="mt-6 p-4 border-red-300/40 bg-red-50/40">
            <p class="font-medium text-red-700">Tidak Memenuhi Syarat</p>
            <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                <li v-for="r in result.ineligibility_reasons" :key="r">{{ r }}</li>
            </ul>
        </Card>

        <Card v-else variant="elevated" class="mt-6 p-6">
            <p class="eyebrow">Skor akhir</p>
            <p class="display text-gradient mt-2">
                {{ result.score?.toFixed?.(2) ?? result.score }}
            </p>
            <p class="mt-1 text-sm text-[var(--muted)] capitalize">
                Kategori: <span class="tag tag-success">{{ result.status ?? result.category }}</span>
                <span v-if="result.rank" class="mono"> · Rank {{ result.rank }}</span>
            </p>
        </Card>

        <Card variant="elevated" class="mt-6 p-6">
            <p class="font-medium mb-3">Input crisp</p>
            <pre class="overflow-auto rounded-md bg-[var(--primary-soft)]/40 p-3 text-xs mono">{{ JSON.stringify(result.input_snapshot, null, 2) }}</pre>
        </Card>

        <div class="window mt-6">
            <div class="window-bar flex items-center justify-between">
                <span class="window-title text-xs">Rule yang dieksekusi</span>
                <Button size="sm" variant="ghost" @click="showAll = !showAll">
                    {{ showAll ? "Sembunyikan α=0" : "Tampilkan semua rule" }}
                </Button>
            </div>
            <div class="window-body p-0 overflow-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)] bg-[var(--surface)]">
                        <tr>
                            <th class="px-4 py-3">Rule</th>
                            <th class="px-4 py-3">Consequent</th>
                            <th class="px-4 py-3 text-right">α</th>
                            <th class="px-4 py-3 text-right">z</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="e in (showAll ? evaluations : evaluations.filter((x) => Number(x.alpha) > 0))" :key="e.rule_code">
                            <td class="px-4 py-3 mono text-xs">{{ e.rule_code }}</td>
                            <td class="px-4 py-3">
                                <span class="tag capitalize">{{ e.consequent }}</span>
                            </td>
                            <td class="px-4 py-3 text-right mono tnum">{{ Number(e.alpha).toFixed(3) }}</td>
                            <td class="px-4 py-3 text-right mono tnum">{{ Number(e.z).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
