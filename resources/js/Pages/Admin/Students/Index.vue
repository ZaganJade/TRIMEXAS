<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Button from "@/components/ui/Button.vue";
import Card from "@/components/ui/Card.vue";
import Input from "@/components/ui/Input.vue";

const props = defineProps({
    students: { type: Object, required: true },
    q: { type: String, default: "" },
});

const search = ref(props.q);

function doSearch() {
    router.get(route("admin.students.index"), { q: search.value }, { preserveState: true });
}

const logoutForm = useForm({});
function logout() {
    logoutForm.post(route("logout"));
}
</script>

<template>
    <Head title="Mahasiswa" />

    <div class="min-h-screen bg-[var(--background)]">
        <header class="border-b border-[var(--border)]">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link :href="route('admin.dashboard')" class="font-display text-lg font-semibold tracking-tight">
                    Trimexas <span class="text-[var(--muted)] text-sm font-normal">/ Admin</span>
                </Link>
                <Button variant="ghost" size="sm" @click="logout">Logout</Button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10 space-y-4">
            <div class="flex items-baseline justify-between">
                <h1 class="font-display text-3xl font-semibold tracking-tight">Daftar Mahasiswa</h1>
                <Link :href="route('admin.students.create')" class="text-sm text-[var(--primary)] hover:underline">+ Tambah</Link>
            </div>

            <form class="flex gap-2" @submit.prevent="doSearch">
                <Input v-model="search" placeholder="Cari nama atau NIM" />
                <Button type="submit" variant="secondary">Cari</Button>
            </form>

            <Card variant="elevated" class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-[var(--muted)]">
                        <tr>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Semester</th>
                            <th class="px-4 py-3">IPK</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        <tr v-for="s in students.data" :key="s.id">
                            <td class="px-4 py-3 font-mono text-xs">{{ s.nim }}</td>
                            <td class="px-4 py-3">{{ s.full_name }}</td>
                            <td class="px-4 py-3">{{ s.semester }}</td>
                            <td class="px-4 py-3">{{ s.ipk }}</td>
                            <td class="px-4 py-3">{{ s.approval_status }}</td>
                            <td class="px-4 py-3">
                                <Link :href="route('admin.students.show', s.id)" class="text-[var(--primary)] hover:underline">Detail</Link>
                            </td>
                        </tr>
                        <tr v-if="!students.data.length">
                            <td colspan="6" class="px-4 py-10 text-center text-[var(--muted)]">Tidak ada mahasiswa.</td>
                        </tr>
                    </tbody>
                </table>
            </Card>
        </main>
    </div>
</template>
