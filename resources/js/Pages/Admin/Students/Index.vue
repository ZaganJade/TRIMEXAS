<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import Input from "@/components/ui/Input.vue";
import { Search, Plus } from "@lucide/vue";

const props = defineProps({
    students: { type: Object, required: true },
    q: { type: String, default: "" },
});

const search = ref(props.q);

function doSearch() {
    router.get(route("admin.students.index"), { q: search.value }, { preserveState: true });
}
</script>

<template>
    <Head title="Mahasiswa" />

    <AdminLayout active="students">
        <header class="reveal-stagger" style="--delay: 0ms">
            <div class="flex items-baseline justify-between">
                <div>
                    <span class="section-label">Manajemen Mahasiswa</span>
                    <h1 class="display mt-4 text-[clamp(2rem,4vw,2.5rem)] text-[var(--ink)]">
                        Daftar Mahasiswa
                    </h1>
                </div>
                <Link :href="route('admin.students.create')">
                    <Button variant="primary" size="sm">
                        <Plus :size="16" class="mr-1.5" />
                        Tambah
                    </Button>
                </Link>
            </div>
        </header>

        <form
            class="reveal-stagger mt-6 flex gap-2"
            style="--delay: 90ms"
            @submit.prevent="doSearch"
        >
            <div class="relative flex-1">
                <Search
                    :size="16"
                    class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)]"
                />
                <Input
                    v-model="search"
                    placeholder="Cari nama atau NIM"
                    class="pl-9"
                />
            </div>
            <Button type="submit" variant="secondary">Cari</Button>
        </form>

        <div class="window reveal-stagger mt-6 overflow-hidden" style="--delay: 180ms">
            <div class="window-bar">
                <div class="flex items-center gap-1.5">
                    <span class="window-dot bg-red-500"></span>
                    <span class="window-dot bg-yellow-500"></span>
                    <span class="window-dot bg-green-500"></span>
                </div>
                <span class="window-title">students.db</span>
            </div>
            <div class="window-body overflow-x-auto">
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
                        <tr v-for="s in students.data" :key="s.id" class="hover:bg-[var(--surface-2)]">
                            <td class="px-4 py-3 font-mono text-xs text-[var(--muted)]">{{ s.nim }}</td>
                            <td class="px-4 py-3 font-medium">{{ s.full_name }}</td>
                            <td class="px-4 py-3 text-[var(--muted)]">{{ s.semester }}</td>
                            <td class="px-4 py-3 font-mono text-[var(--muted)] tnum">{{ s.ipk }}</td>
                            <td class="px-4 py-3">
                                <span class="tag tag-primary text-xs">{{ s.approval_status }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="route('admin.students.show', s.id)"
                                    class="text-[var(--primary)] hover:underline"
                                >
                                    Detail
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!students.data.length">
                            <td colspan="6" class="px-4 py-10 text-center text-[var(--muted)]">
                                Tidak ada mahasiswa.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
