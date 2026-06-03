<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
import MahasiswaLayout from "@/Layouts/MahasiswaLayout.vue";
import Button from "@/components/ui/Button.vue";
import Input from "@/components/ui/Input.vue";
import { ListOrdered, Search } from "@lucide/vue";

const props = defineProps({
    batchLabel: { type: String, default: null },
    rankings: { type: Array, default: () => [] },
    q: { type: String, default: null },
});

const search = ref(props.q ?? "");
function doSearch() {
    router.get(route("mahasiswa.ranking.index"), search.value ? { q: search.value } : {}, {
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Ranking Beasiswa" />

    <MahasiswaLayout active="ranking">
        <div class="space-y-7">
            <!-- Heading -->
            <header class="reveal-stagger" style="--delay: 0ms">
                <div class="flex items-center gap-3">
                    <span class="bento-icon" style="background: var(--primary-soft); color: var(--primary); border-color: color-mix(in oklab, var(--primary) 28%, transparent)">
                        <ListOrdered :size="20" />
                    </span>
                    <div>
                        <span class="section-label">Hasil Seleksi</span>
                        <h1 class="display mt-1 text-[clamp(1.8rem,4vw,2.4rem)] text-[var(--ink)]">
                            Ranking Beasiswa
                        </h1>
                    </div>
                </div>
                <p class="mt-3 text-[14.5px] text-[var(--muted)]">
                    <template v-if="batchLabel">
                        Batch terbaru: <span class="tag tag-primary">{{ batchLabel }}</span>
                    </template>
                    <template v-else>
                        Belum ada hasil seleksi yang completed.
                    </template>
                </p>
            </header>

            <!-- Search -->
            <section v-if="batchLabel" class="reveal-stagger" style="--delay: 80ms">
                <form class="flex gap-2" @submit.prevent="doSearch">
                    <div class="relative flex-1">
                        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)]" />
                        <Input v-model="search" placeholder="Cari nama mahasiswa…" class="!pl-10" />
                    </div>
                    <Button type="submit" variant="secondary">Cari</Button>
                </form>
            </section>

            <!-- Table -->
            <section class="reveal-stagger" style="--delay: 160ms">
                <div v-if="batchLabel" class="window">
                    <div class="window-bar">
                        <span class="window-dot" style="background:#fb7185"></span>
                        <span class="window-dot" style="background:#fbbf24"></span>
                        <span class="window-dot" style="background:#34d399"></span>
                        <span class="window-title">ranking-{{ batchLabel }}.json</span>
                    </div>
                    <div class="window-body !p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-[14px]">
                                <thead class="border-b border-[var(--border)] bg-[var(--surface)]">
                                    <tr class="text-left">
                                        <th class="px-5 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)] w-16">#</th>
                                        <th class="px-5 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Nama</th>
                                        <th class="px-5 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)] text-right">Skor</th>
                                        <th class="px-5 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[var(--border)]">
                                    <tr v-for="(r, idx) in rankings" :key="r.name" class="rank-row">
                                        <td class="px-5 py-4">
                                            <span class="rank-pos">{{ idx + 1 }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="avatar">
                                                    {{ r.name.charAt(0).toUpperCase() }}
                                                </span>
                                                <span class="text-[var(--ink)] font-medium">{{ r.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <span class="score-dial text-[1.2rem] tnum text-[var(--ink)]">
                                                {{ r.score !== null ? Number(r.score).toFixed(2) : '—' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span
                                                v-if="r.status"
                                                class="tag"
                                                :class="{
                                                    'tag-success': r.status === 'lolos' || r.status === 'cadangan',
                                                    'tag-warning': r.status === 'tidak memenuhi syarat',
                                                }"
                                            >
                                                {{ r.status }}
                                            </span>
                                            <span v-else class="text-[var(--muted)]">—</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!rankings.length">
                                        <td colspan="4" class="px-5 py-12 text-center text-[var(--muted)]">
                                            Tidak ada data ranking.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-else class="bento col-6 text-center">
                    <p class="text-[15px] text-[var(--muted)]">
                        Belum ada hasil seleksi yang completed.
                    </p>
                </div>
            </section>
        </div>
    </MahasiswaLayout>
</template>
