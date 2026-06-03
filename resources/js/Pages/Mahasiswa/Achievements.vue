<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import MahasiswaLayout from "@/Layouts/MahasiswaLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import Select from "@/components/ui/Select.vue";
import { Trophy, CheckCircle2, Edit2, Trash2 } from "@lucide/vue";

const props = defineProps({
    achievements: { type: Array, default: () => [] },
    aggregate: { type: Object, default: () => ({ akademis: 0, non_akademis: 0 }) },
    levels: { type: Array, default: () => [] },
    ranks: { type: Array, default: () => [] },
});

const form = useForm({
    title: "",
    category: "akademis",
    level: "nasional",
    rank: "juara_2",
    year: new Date().getFullYear(),
});

const editing = ref(null);

function submit() {
    if (editing.value) {
        form.put(route("mahasiswa.achievements.update", editing.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                editing.value = null;
                form.reset();
            },
        });
        return;
    }
    form.post(route("mahasiswa.achievements.store"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function startEdit(a) {
    editing.value = a;
    form.title = a.title;
    form.category = a.category;
    form.level = a.level;
    form.rank = a.rank;
    form.year = a.year;
}

function destroy(a) {
    if (!confirm(`Hapus entri "${a.title}"?`)) return;
    useForm({}).delete(route("mahasiswa.achievements.destroy", a.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Prestasi Saya" />

    <MahasiswaLayout active="achievements">
        <div class="space-y-7">
            <!-- Heading -->
            <header class="reveal-stagger" style="--delay: 0ms">
                <div class="flex items-center gap-3">
                    <span class="bento-icon" style="background: var(--accent-2); color: var(--accent); border-color: color-mix(in oklab, var(--accent) 30%, transparent)">
                        <Trophy :size="20" />
                    </span>
                    <div>
                        <span class="section-label">Capaian</span>
                        <h1 class="display mt-1 text-[clamp(1.8rem,4vw,2.4rem)] text-[var(--ink)]">
                            Prestasi Saya
                        </h1>
                    </div>
                </div>
                <p class="mt-3 text-[14.5px] text-[var(--muted)]">
                    Maks 5 entri (akademis + non-akademis). Skor agregat di-cap 50 per kategori.
                </p>
            </header>

            <!-- Aggregate -->
            <section class="reveal-stagger bento-grid" style="--delay: 80ms">
                <article class="bento col-3">
                    <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Akademis</span>
                    <p class="display mt-2 text-[2.2rem] leading-none text-[var(--ink)] tnum">{{ aggregate.akademis }}</p>
                </article>
                <article class="bento col-3">
                    <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Non-akademis</span>
                    <p class="display mt-2 text-[2.2rem] leading-none text-[var(--ink)] tnum">{{ aggregate.non_akademis }}</p>
                </article>
            </section>

            <!-- Form -->
            <section class="reveal-stagger" style="--delay: 160ms">
                <Card variant="elevated" class="p-6">
                    <h2 class="display-md text-[1.1rem] text-[var(--ink)]">{{ editing ? "Ubah entri" : "Tambah entri baru" }}</h2>
                    <form class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2" @submit.prevent="submit">
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="title" required>Judul</Label>
                            <Input id="title" v-model="form.title" :invalid="!!form.errors.title" placeholder="Contoh: Juara 1 Lomba Karya Ilmiah" />
                            <p v-if="form.errors.title" class="text-[13px]" style="color: var(--danger)">{{ form.errors.title }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="category" required>Kategori</Label>
                            <Select id="category" v-model="form.category">
                                <option value="akademis">Akademis</option>
                                <option value="non_akademis">Non-akademis</option>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="year" required>Tahun</Label>
                            <Input id="year" v-model.number="form.year" type="number" min="2000" max="2100" />
                        </div>
                        <div class="space-y-2">
                            <Label for="level" required>Level</Label>
                            <Select id="level" v-model="form.level">
                                <option v-for="l in levels" :key="l" :value="l">{{ l }}</option>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="rank" required>Peringkat</Label>
                            <Select id="rank" v-model="form.rank">
                                <option v-for="r in ranks" :key="r" :value="r">{{ r }}</option>
                            </Select>
                            <p v-if="form.errors.rank" class="text-[13px]" style="color: var(--danger)">{{ form.errors.rank }}</p>
                        </div>
                        <div class="sm:col-span-2 flex flex-wrap gap-2">
                            <Button type="submit" :disabled="form.processing" variant="primary">
                                {{ form.processing ? "Menyimpan…" : (editing ? "Simpan perubahan" : "Tambah entri") }}
                            </Button>
                            <Button v-if="editing" variant="ghost" type="button" @click="() => { editing = null; form.reset(); }">
                                Batal
                            </Button>
                        </div>
                    </form>
                </Card>
            </section>

            <!-- Table -->
            <section class="reveal-stagger" style="--delay: 240ms">
                <div class="window">
                    <div class="window-bar">
                        <span class="window-dot" style="background:#fb7185"></span>
                        <span class="window-dot" style="background:#fbbf24"></span>
                        <span class="window-dot" style="background:#34d399"></span>
                        <span class="window-title">daftar-prestasi.json</span>
                    </div>
                    <div class="window-body !p-0 overflow-x-auto">
                        <table class="w-full text-[14px]">
                            <thead class="border-b border-[var(--border)] bg-[var(--surface)]">
                                <tr class="text-left">
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Judul</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Kategori</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Level</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Peringkat</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)] text-right">Skor</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)] text-center">Verif</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                <tr v-for="a in achievements" :key="a.id" class="hover:bg-[var(--surface)] transition-colors">
                                    <td class="px-4 py-3 text-[var(--foreground)]">{{ a.title }}</td>
                                    <td class="px-4 py-3 text-[var(--muted)] capitalize">{{ a.category }}</td>
                                    <td class="px-4 py-3 text-[var(--muted)] capitalize">{{ a.level }}</td>
                                    <td class="px-4 py-3 text-[var(--muted)]">{{ a.rank }}</td>
                                    <td class="px-4 py-3 text-right mono tnum text-[var(--ink)]">{{ a.score }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <CheckCircle2 v-if="a.verified_by_admin" :size="16" style="color: var(--success); margin: 0 auto" />
                                        <span v-else class="text-[var(--muted)]">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div v-if="!a.verified_by_admin" class="flex items-center justify-end gap-1">
                                            <button
                                                type="button"
                                                @click="startEdit(a)"
                                                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-[13px] text-[var(--muted)] hover:bg-[var(--surface-2)] hover:text-[var(--foreground)] transition-colors"
                                            >
                                                <Edit2 :size="13" />
                                                <span>Edit</span>
                                            </button>
                                            <button
                                                type="button"
                                                @click="destroy(a)"
                                                class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-[13px] hover:bg-[var(--danger)] hover:text-white transition-colors"
                                                style="color: var(--danger)"
                                            >
                                                <Trash2 :size="13" />
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!achievements.length">
                                    <td colspan="7" class="px-4 py-12 text-center text-[var(--muted)]">
                                        Belum ada entri prestasi.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </MahasiswaLayout>
</template>
