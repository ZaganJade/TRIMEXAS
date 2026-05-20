<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";

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

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Ranking Beasiswa" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
                <Link :href="route('mahasiswa.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Mahasiswa</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6 py-10 space-y-4">
            <div>
                <h1 class="font-display text-3xl font-semibold tracking-tight">Ranking</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">
                    <template v-if="batchLabel">
                        Batch terbaru: <span class="font-medium">{{ batchLabel }}</span>
                    </template>
                    <template v-else>
                        Belum ada hasil seleksi.
                    </template>
                </p>
            </div>

            <form v-if="batchLabel" class="flex gap-2" @submit.prevent="doSearch">
                <Input v-model="search" placeholder="Cari nama" />
                <Button type="submit" variant="secondary">Cari</Button>
            </form>

            <Card v-if="batchLabel" variant="elevated" class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3 text-right">Skor</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="r in rankings" :key="r.name">
                            <td class="px-4 py-3">{{ r.name }}</td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ r.score !== null ? Number(r.score).toFixed(2) : '—' }}
                            </td>
                            <td class="px-4 py-3 capitalize">{{ r.status ?? '—' }}</td>
                        </tr>
                        <tr v-if="!rankings.length">
                            <td colspan="3" class="px-4 py-10 text-center text-[var(--muted)]">
                                Tidak ada data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Card>

            <Card v-else variant="outline" class="p-6 text-center text-sm text-[var(--muted)]">
                Belum ada hasil seleksi yang completed.
            </Card>
        </main>
    </div>
</template>
