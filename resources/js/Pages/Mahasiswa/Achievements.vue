<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";
import Label from "@/components/ui/Label.vue";
import Select from "@/components/ui/Select.vue";

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

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Prestasi Saya" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
                <Link :href="route('mahasiswa.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Mahasiswa</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-6 py-10 space-y-6">
            <div>
                <h1 class="font-display text-3xl font-semibold tracking-tight">Prestasi Saya</h1>
                <p class="mt-2 text-sm text-[var(--muted)]">
                    Maks 5 entri (akademis + non-akademis). Skor agregat di-cap 50 per kategori.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <Card variant="elevated" class="p-5">
                    <p class="text-xs text-[var(--muted)] uppercase tracking-wide">Akademis</p>
                    <p class="mt-2 font-display text-3xl font-semibold">{{ aggregate.akademis }}</p>
                </Card>
                <Card variant="elevated" class="p-5">
                    <p class="text-xs text-[var(--muted)] uppercase tracking-wide">Non-akademis</p>
                    <p class="mt-2 font-display text-3xl font-semibold">{{ aggregate.non_akademis }}</p>
                </Card>
            </div>

            <Card variant="elevated" class="p-6">
                <p class="font-medium">{{ editing ? "Ubah entri" : "Tambah entri baru" }}</p>
                <form class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2" @submit.prevent="submit">
                    <div class="sm:col-span-2 space-y-1.5">
                        <Label for="title" required>Judul</Label>
                        <Input id="title" v-model="form.title" :invalid="!!form.errors.title" />
                        <p v-if="form.errors.title" class="text-xs text-red-600">{{ form.errors.title }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="category" required>Kategori</Label>
                        <select id="category" v-model="form.category" class="h-10 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm">
                            <option value="akademis">Akademis</option>
                            <option value="non_akademis">Non-akademis</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="year" required>Tahun</Label>
                        <Input id="year" v-model.number="form.year" type="number" min="2000" max="2100" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="level" required>Level</Label>
                        <select id="level" v-model="form.level" class="h-10 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm">
                            <option v-for="l in levels" :key="l" :value="l">{{ l }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <Label for="rank" required>Peringkat</Label>
                        <select id="rank" v-model="form.rank" class="h-10 w-full rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 text-sm">
                            <option v-for="r in ranks" :key="r" :value="r">{{ r }}</option>
                        </select>
                        <p v-if="form.errors.rank" class="text-xs text-red-600">{{ form.errors.rank }}</p>
                    </div>
                    <div class="sm:col-span-2 flex gap-2">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? "Menyimpan…" : (editing ? "Simpan perubahan" : "Tambah entri") }}
                        </Button>
                        <Button v-if="editing" variant="ghost" type="button" @click="() => { editing = null; form.reset(); }">
                            Batal
                        </Button>
                    </div>
                </form>
            </Card>

            <Card variant="elevated" class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Level</th>
                            <th class="px-4 py-3">Peringkat</th>
                            <th class="px-4 py-3 text-right">Skor</th>
                            <th class="px-4 py-3">Verified</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="a in achievements" :key="a.id">
                            <td class="px-4 py-3">{{ a.title }}</td>
                            <td class="px-4 py-3">{{ a.category }}</td>
                            <td class="px-4 py-3">{{ a.level }}</td>
                            <td class="px-4 py-3">{{ a.rank }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ a.score }}</td>
                            <td class="px-4 py-3">
                                <span v-if="a.verified_by_admin" class="text-emerald-600">✓</span>
                                <span v-else class="text-[var(--muted)]">—</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button v-if="!a.verified_by_admin" size="sm" variant="ghost" @click="startEdit(a)">Edit</Button>
                                <Button v-if="!a.verified_by_admin" size="sm" variant="danger" class="ml-1" @click="destroy(a)">Hapus</Button>
                            </td>
                        </tr>
                        <tr v-if="!achievements.length">
                            <td colspan="7" class="px-4 py-10 text-center text-[var(--muted)]">
                                Belum ada entri prestasi.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </Card>
        </main>
    </div>
</template>
