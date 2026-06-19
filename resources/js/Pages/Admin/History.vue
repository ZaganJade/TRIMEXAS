<script setup>
import { computed, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Button from "@/components/ui/Button.vue";
import DataTable from "@/components/ui/DataTable.vue";
import Select from "@/components/ui/Select.vue";
import { Plus } from "@lucide/vue";

const props = defineProps({
    batches: { type: Array, default: () => [] },
    status: { type: String, default: null },
});

// Status filter is client-side now — selecting a value never reloads the page.
const statusFilter = ref(props.status ?? "all");
const statusOptions = [
    { value: "all", label: "Semua status" },
    { value: "queued", label: "Antri" },
    { value: "running", label: "Berjalan" },
    { value: "completed", label: "Selesai" },
    { value: "failed", label: "Gagal" },
];

const filteredRows = computed(() => {
    if (!statusFilter.value || statusFilter.value === "all") return props.batches;
    return props.batches.filter((b) => b.status === statusFilter.value);
});

const STATUS_META = {
    queued: { label: "Antri", cls: "tag-primary" },
    running: { label: "Berjalan", cls: "tag-warning" },
    completed: { label: "Selesai", cls: "tag-success" },
    failed: { label: "Gagal", cls: "tag-error" },
};

function statusMeta(status) {
    return STATUS_META[status] ?? { label: status, cls: "tag-primary" };
}

function formatDate(iso) {
    if (!iso) return "—";
    return new Date(iso).toLocaleString("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    });
}

// Column definitions for the data table.
const columns = [
    { key: "label", header: "Label", sortable: true },
    { key: "triggered_by", header: "Dijalankan oleh", sortable: true },
    { key: "status", header: "Status", sortable: true },
    { key: "total_candidates", header: "Total", sortable: true, align: "right" },
    { key: "total_eligible", header: "Lolos Syarat", sortable: true, align: "right" },
    { key: "created_at", header: "Tanggal", sortable: true },
    { key: "actions", header: "", sortable: false, align: "right" },
];
</script>

<template>
    <Head title="Riwayat Batch" />
    <AdminLayout active="history">
        <div class="window">
            <div class="window-bar">
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-dot" />
                <span class="window-title">Riwayat Batch Seleksi</span>
            </div>
            <div class="window-body flex flex-wrap items-center justify-between gap-3">
                <div class="w-full sm:w-56">
                    <Select
                        v-model="statusFilter"
                        :options="statusOptions"
                        placeholder="Semua status"
                    />
                </div>
                <Button as="a" :href="route('admin.selection.run')" variant="primary" size="sm">
                    <Plus :size="14" />
                    Batch baru
                </Button>
            </div>
        </div>

        <div class="window mt-6">
            <div class="window-body !p-0">
                <DataTable
                    :rows="filteredRows"
                    :columns="columns"
                    :search-keys="['label', 'triggered_by', 'status']"
                    search-placeholder="Cari batch, nama, atau status…"
                    :page-size="10"
                    empty-text="Belum ada batch yang cocok."
                >
                    <template #cell-label="{ row }">
                        <span class="font-medium text-[var(--ink)]">{{ row.label }}</span>
                    </template>

                    <template #cell-status="{ row }">
                        <span
                            class="tag mono text-xs uppercase"
                            :class="statusMeta(row.status).cls"
                        >
                            {{ statusMeta(row.status).label }}
                        </span>
                    </template>

                    <template #cell-total_candidates="{ value }">
                        <span class="mono tnum text-[var(--foreground)]">{{ value ?? 0 }}</span>
                    </template>

                    <template #cell-total_eligible="{ row }">
                        <span class="mono tnum text-[var(--foreground)]">
                            {{ row.total_eligible ?? 0 }}<span class="text-[var(--muted)]">/{{ row.total_candidates ?? 0 }}</span>
                        </span>
                    </template>

                    <template #cell-created_at="{ value }">
                        <span class="text-xs text-[var(--muted)]">{{ formatDate(value) }}</span>
                    </template>

                    <template #cell-actions="{ row }">
                        <Link
                            :href="route('admin.selection.show', row.id)"
                            class="text-sm font-medium text-[var(--primary)] hover:underline"
                        >
                            Lihat
                        </Link>
                    </template>
                </DataTable>
            </div>
        </div>
    </AdminLayout>
</template>
