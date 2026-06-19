<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import MahasiswaLayout from "@/Layouts/MahasiswaLayout.vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import Select from "@/components/ui/Select.vue";
import { Trophy, CheckCircle2, Edit2, Trash2, Upload, FileText, Download, X } from "@lucide/vue";

const props = defineProps({
    achievements: { type: Array, default: () => [] },
    aggregate: { type: Object, default: () => ({ akademis: 0, non_akademis: 0 }) },
    levels: { type: Array, default: () => [] },
    ranks: { type: Array, default: () => [] },
});

const MAX_FILE_MB = 5;

const form = useForm({
    title: "",
    category: "akademis",
    level: "nasional",
    rank: "juara_2",
    year: new Date().getFullYear(),
    certificate: null,
});

const editing = ref(null);
const certificatePreview = ref(null);

const LEVEL_LABELS = {
    internasional: "Internasional",
    nasional: "Nasional",
    provinsi: "Provinsi",
    kabupaten: "Kabupaten/Kota",
};
const RANK_LABELS = {
    juara_1: "Juara 1",
    juara_2: "Juara 2",
    juara_3: "Juara 3",
    partisipasi: "Partisipasi",
};
function levelLabel(v) { return LEVEL_LABELS[v] ?? v; }
function rankLabel(v) { return RANK_LABELS[v] ?? v; }

function onFileChange(e) {
    const file = e.target.files?.[0] ?? null;
    form.certificate = file;
    if (file) {
        certificatePreview.value = {
            name: file.name,
            size: (file.size / 1024).toFixed(1),
        };
    } else {
        certificatePreview.value = null;
    }
}

function clearFile() {
    form.certificate = null;
    certificatePreview.value = null;
    const input = document.getElementById("certificate");
    if (input) input.value = "";
}

function submit() {
    if (editing.value) {
        form.put(route("mahasiswa.achievements.update", editing.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                editing.value = null;
                form.reset();
                certificatePreview.value = null;
            },
        });
        return;
    }
    form.post(route("mahasiswa.achievements.store"), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            certificatePreview.value = null;
        },
    });
}

function startEdit(a) {
    editing.value = a;
    form.title = a.title;
    form.category = a.category;
    form.level = a.level;
    form.rank = a.rank;
    form.year = a.year;
    form.certificate = null;
    certificatePreview.value = a.certificate_path
        ? { name: a.certificate_original_name, size: a.certificate_size ? (a.certificate_size / 1024).toFixed(1) : null, existing: true }
        : null;
}

function destroy(a) {
    if (!confirm(`Hapus entri "${a.title}"?`)) return;
    useForm({}).delete(route("mahasiswa.achievements.destroy", a.id), { preserveScroll: true });
}

function cancelEdit() {
    editing.value = null;
    form.reset();
    certificatePreview.value = null;
}

const usedSlots = computed(() => props.achievements.length);
const slotsLeft = computed(() => Math.max(0, 5 - usedSlots.value));
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
                    Unggah sertifikat PDF (maks {{ MAX_FILE_MB }}MB) sebagai bukti capaian.
                </p>
            </header>

            <!-- Aggregate -->
            <div class="bento-grid reveal-stagger" style="--delay: 80ms">
                <article class="bento col-4">
                    <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Akademis</span>
                    <p class="display mt-2 text-[2.2rem] leading-none text-[var(--ink)] tnum">{{ aggregate.akademis }}</p>
                </article>
                <article class="bento col-4">
                    <span class="mono text-[10px] uppercase tracking-[0.2em] text-[var(--muted)]">Non-akademis</span>
                    <p class="display mt-2 text-[2.2rem] leading-none text-[var(--ink)] tnum">{{ aggregate.non_akademis }}</p>
                </article>
            </div>

            <!-- Form -->
            <section class="reveal-stagger" style="--delay: 160ms">
                <Card variant="elevated" class="p-6">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h2 class="display-md text-[1.1rem] text-[var(--ink)]">
                            {{ editing ? "Ubah entri" : "Tambah entri baru" }}
                        </h2>
                        <span class="tag" :class="slotsLeft > 0 ? 'tag-primary' : 'tag-warning'">
                            Sisa slot: {{ slotsLeft }} / 5
                        </span>
                    </div>
                    <form class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2" enctype="multipart/form-data" @submit.prevent="submit">
                        <div class="space-y-2 sm:col-span-2">
                            <Label for="title" required>Judul</Label>
                            <Input id="title" v-model="form.title" :invalid="!!form.errors.title" placeholder="Contoh: Juara 1 Lomba Karya Ilmiah" />
                            <p v-if="form.errors.title" class="text-[13px]" style="color: var(--danger)">{{ form.errors.title }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="category" required>Kategori</Label>
                            <Select id="category" v-model="form.category" :options="[
                                { value: 'akademis', label: 'Akademis' },
                                { value: 'non_akademis', label: 'Non-akademis' },
                            ]" />
                        </div>
                        <div class="space-y-2">
                            <Label for="year" required>Tahun</Label>
                            <Input id="year" v-model.number="form.year" type="number" min="2000" max="2100" />
                        </div>
                        <div class="space-y-2">
                            <Label for="level" required>Level</Label>
                            <Select id="level" v-model="form.level" :options="levels.map(l => ({ value: l, label: levelLabel(l) }))" />
                        </div>
                        <div class="space-y-2">
                            <Label for="rank" required>Peringkat</Label>
                            <Select id="rank" v-model="form.rank" :options="ranks.map(r => ({ value: r, label: rankLabel(r) }))" />
                            <p v-if="form.errors.rank" class="text-[13px]" style="color: var(--danger)">{{ form.errors.rank }}</p>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <Label for="certificate">
                                Sertifikat (PDF) {{ editing ? '— kosongkan jika tidak ingin mengganti' : '' }}
                            </Label>
                            <label
                                for="certificate"
                                class="flex flex-col items-center justify-center gap-2 rounded-[var(--radius-input)] border-2 border-dashed border-[var(--border)] bg-[var(--surface-2)]/40 px-4 py-6 cursor-pointer hover:border-[var(--primary)] hover:bg-[var(--primary-soft)]/30 transition-colors"
                            >
                                <Upload :size="22" class="text-[var(--muted)]" />
                                <span class="text-[13.5px] text-[var(--muted)]">
                                    Klik untuk pilih file PDF (maks {{ MAX_FILE_MB }}MB)
                                </span>
                                <span class="mono text-[11px] text-[var(--muted)]">.pdf only</span>
                                <input
                                    id="certificate"
                                    type="file"
                                    accept="application/pdf,.pdf"
                                    class="hidden"
                                    @change="onFileChange"
                                />
                            </label>
                            <div v-if="certificatePreview" class="mt-2 flex items-center justify-between gap-2 rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-2 text-[13px]">
                                <div class="flex items-center gap-2 min-w-0">
                                    <FileText :size="14" class="shrink-0 text-[var(--primary)]" />
                                    <span class="truncate">{{ certificatePreview.name }}</span>
                                    <span v-if="certificatePreview.size" class="text-[var(--muted)] mono shrink-0">{{ certificatePreview.size }} KB</span>
                                    <span v-if="certificatePreview.existing" class="tag tag-primary text-[10px] shrink-0">tersimpan</span>
                                </div>
                                <button type="button" class="text-[var(--muted)] hover:text-[var(--danger)]" @click="clearFile">
                                    <X :size="14" />
                                </button>
                            </div>
                            <p v-if="form.errors.certificate" class="text-[13px]" style="color: var(--danger)">{{ form.errors.certificate }}</p>
                        </div>

                        <div class="sm:col-span-2 flex flex-wrap gap-2">
                            <Button type="submit" :disabled="form.processing || (!editing && slotsLeft <= 0)" variant="primary">
                                {{ form.processing ? "Menyimpan…" : (editing ? "Simpan perubahan" : "Tambah entri") }}
                            </Button>
                            <Button v-if="editing" variant="ghost" type="button" @click="cancelEdit">Batal</Button>
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
                    <div class="window-body !p-0 table-scroll">
                        <table class="w-full text-[14px]">
                            <thead class="border-b border-[var(--border)] bg-[var(--surface)]">
                                <tr class="text-left">
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Judul</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Kategori</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Level</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Peringkat</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)] text-right">Skor</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)]">Sertifikat</th>
                                    <th class="px-4 py-3 mono text-[10px] uppercase tracking-[0.16em] text-[var(--muted)] text-center">Verif</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                <tr v-for="a in achievements" :key="a.id" class="hover:bg-[var(--surface)] transition-colors">
                                    <td class="px-4 py-3 text-[var(--foreground)]">{{ a.title }}</td>
                                    <td class="px-4 py-3 text-[var(--muted)] capitalize">{{ a.category === 'non_akademis' ? 'Non-akademis' : 'Akademis' }}</td>
                                    <td class="px-4 py-3 text-[var(--muted)] capitalize">{{ levelLabel(a.level) }}</td>
                                    <td class="px-4 py-3 text-[var(--muted)]">{{ rankLabel(a.rank) }}</td>
                                    <td class="px-4 py-3 text-right mono tnum text-[var(--ink)]">{{ a.score }}</td>
                                    <td class="px-4 py-3">
                                        <a
                                            v-if="a.certificate_path"
                                            :href="`/storage/${a.certificate_path}`"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex items-center gap-1.5 text-[var(--primary)] hover:underline text-[13px]"
                                        >
                                            <Download :size="13" />
                                            <span class="truncate max-w-[140px]">{{ a.certificate_original_name || 'Lihat' }}</span>
                                        </a>
                                        <span v-else class="text-[var(--muted)] text-[13px]">—</span>
                                    </td>
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
                                    <td colspan="8" class="px-4 py-12 text-center text-[var(--muted)]">
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
