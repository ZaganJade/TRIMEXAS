<script setup>
import { computed, ref, watch } from "vue";
import { cn } from "@/lib/utils";
import { Search, ChevronUp, ChevronDown, ChevronLeft, ChevronRight } from "@lucide/vue";

/**
 * Reusable client-side data table.
 * - Search, sort, and pagination are all handled locally — no page reload,
 *   so the keyboard search field keeps focus while typing.
 *
 * Props:
 *  - rows:         Array<Object>          the full dataset
 *  - columns:      Array<Column>          column definitions
 *  - searchKeys:   Array<String>          row keys scanned by the search box
 *  - searchPlaceholder: String
 *  - pageSize:     Number (default 10)
 *  - emptyText:    String                 message when no rows match
 *  - initialSort:  { key, dir } | null    dir = 'asc' | 'desc'
 *
 * Column shape:
 *  { key, header, sortable?(true), align?('left'|'right'|'center'),
 *    accessor?(row) => value, format?(value, row) => string }
 *
 * Slots: `cell-<key>` for custom cell rendering (scoped: { row, value }).
 */
const props = defineProps({
    rows: { type: Array, default: () => [] },
    columns: { type: Array, required: true },
    searchKeys: { type: Array, default: () => [] },
    searchPlaceholder: { type: String, default: "Cari…" },
    pageSize: { type: Number, default: 10 },
    emptyText: { type: String, default: "Tidak ada data." },
    initialSort: { type: Object, default: null },
});

/* ---------- search ---------- */
const query = ref("");
const normalizedQuery = computed(() => query.value.trim().toLowerCase());

function valueAt(row, col) {
    return col.accessor ? col.accessor(row) : row[col.key];
}

const searched = computed(() => {
    if (!normalizedQuery.value) return props.rows;
    const keys = props.searchKeys.length ? props.searchKeys : props.columns.map((c) => c.key);
    return props.rows.filter((row) =>
        keys.some((k) => {
            const v = row[k];
            return v != null && String(v).toLowerCase().includes(normalizedQuery.value);
        }),
    );
});

/* ---------- sort ---------- */
const sortKey = ref(props.initialSort?.key ?? null);
const sortDir = ref(props.initialSort?.dir ?? "asc");

function toggleSort(col) {
    if (!col.sortable) return;
    if (sortKey.value !== col.key) {
        sortKey.value = col.key;
        sortDir.value = "asc";
    } else if (sortDir.value === "asc") {
        sortDir.value = "desc";
    } else {
        // third click clears the sort
        sortKey.value = null;
        sortDir.value = "asc";
    }
    page.value = 1;
}

const sorted = computed(() => {
    if (!sortKey.value) return searched.value;
    const col = props.columns.find((c) => c.key === sortKey.value);
    if (!col) return searched.value;
    const dir = sortDir.value === "desc" ? -1 : 1;
    return [...searched.value].sort((a, b) => {
        const av = valueAt(a, col);
        const bv = valueAt(b, col);
        if (av == null && bv == null) return 0;
        if (av == null) return 1;
        if (bv == null) return -1;
        if (typeof av === "number" && typeof bv === "number") return (av - bv) * dir;
        return String(av).localeCompare(String(bv), "id", { numeric: true }) * dir;
    });
});

/* ---------- pagination ---------- */
const page = ref(1);
const total = computed(() => sorted.value.length);
const totalPages = computed(() => Math.max(1, Math.ceil(total.value / props.pageSize)));

watch(totalPages, (tp) => {
    if (page.value > tp) page.value = tp;
});

// reset to first page whenever the dataset/filter changes in a way that
// could leave the current page out of range
watch([query, () => props.rows], () => {
    page.value = 1;
});

const paged = computed(() => {
    const start = (page.value - 1) * props.pageSize;
    return sorted.value.slice(start, start + props.pageSize);
});

const rangeStart = computed(() => (total.value === 0 ? 0 : (page.value - 1) * props.pageSize + 1));
const rangeEnd = computed(() => Math.min(page.value * props.pageSize, total.value));

function go(p) {
    page.value = Math.min(Math.max(1, p), totalPages.value);
}

/* compact page number list with ellipsis */
const pageList = computed(() => {
    const tp = totalPages.value;
    const cur = page.value;
    if (tp <= 7) return Array.from({ length: tp }, (_, i) => i + 1);
    if (cur <= 4) return [1, 2, 3, 4, 5, "…", tp];
    if (cur >= tp - 3) return [1, "…", tp - 4, tp - 3, tp - 2, tp - 1, tp];
    return [1, "…", cur - 1, cur, cur + 1, "…", tp];
});

function display(row, col) {
    const v = valueAt(row, col);
    return col.format ? col.format(v, row) : v;
}

function alignClass(col) {
    if (col.align === "right") return "text-right";
    if (col.align === "center") return "text-center";
    return "text-left";
}

function sortIcon(col) {
    if (sortKey.value !== col.key) return null;
    return sortDir.value === "asc" ? "asc" : "desc";
}
</script>

<template>
    <div class="data-table">
        <!-- Toolbar: search (client-side, never reloads the page) -->
        <div class="dt-toolbar">
            <label class="dt-search">
                <Search :size="15" class="dt-search-icon" aria-hidden="true" />
                <input
                    v-model="query"
                    type="search"
                    :placeholder="searchPlaceholder"
                    class="dt-search-input"
                    aria-label="Cari"
                    autocomplete="off"
                />
            </label>
            <div class="dt-meta mono">
                <span v-if="total">{{ rangeStart }}–{{ rangeEnd }} dari {{ total }}</span>
                <span v-else>0 hasil</span>
            </div>
        </div>

        <!-- Table -->
        <div class="dt-scroll">
            <table class="dt-table">
                <thead>
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[alignClass(col), col.sortable && 'is-sortable']"
                            @click="toggleSort(col)"
                        >
                            <span class="th-inner">
                                <span>{{ col.header }}</span>
                                <span v-if="col.sortable" class="th-sort" aria-hidden="true">
                                    <ChevronUp
                                        :size="12"
                                        :class="['sort-icon', sortIcon(col) === 'asc' ? 'is-active' : '']"
                                    />
                                    <ChevronDown
                                        :size="12"
                                        :class="['sort-icon', sortIcon(col) === 'desc' ? 'is-active' : '']"
                                    />
                                </span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in paged" :key="row.id ?? JSON.stringify(row)">
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="alignClass(col)"
                        >
                            <slot :name="`cell-${col.key}`" :row="row" :value="valueAt(row, col)">
                                {{ display(row, col) }}
                            </slot>
                        </td>
                    </tr>
                    <tr v-if="!paged.length">
                        <td :colspan="columns.length" class="dt-empty">{{ emptyText }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="dt-pagination">
            <button
                type="button"
                class="dt-page-btn"
                :disabled="page === 1"
                aria-label="Halaman sebelumnya"
                @click="go(page - 1)"
            >
                <ChevronLeft :size="15" />
            </button>
            <button
                v-for="(p, i) in pageList"
                :key="i"
                type="button"
                class="dt-page-num"
                :class="[p === page && 'is-active', p === '…' && 'is-ellipsis']"
                :disabled="p === '…'"
                @click="typeof p === 'number' && go(p)"
            >
                {{ p }}
            </button>
            <button
                type="button"
                class="dt-page-btn"
                :disabled="page === totalPages"
                aria-label="Halaman berikutnya"
                @click="go(page + 1)"
            >
                <ChevronRight :size="15" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.data-table {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* ---- toolbar ---- */
.dt-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.dt-search {
    position: relative;
    display: flex;
    align-items: center;
    min-width: 16rem;
    flex: 1 1 16rem;
    max-width: 28rem;
}

.dt-search-icon {
    position: absolute;
    left: 0.7rem;
    color: var(--muted);
    pointer-events: none;
}

.dt-search-input {
    width: 100%;
    height: 2.5rem;
    padding: 0 0.9rem 0 2.2rem;
    border-radius: var(--radius-input);
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--foreground);
    font-size: 0.875rem;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.dt-search-input::placeholder {
    color: var(--muted);
}
.dt-search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--primary) 18%, transparent);
}
/* hide the browser-native search clear button so our UX stays consistent */
.dt-search-input::-webkit-search-cancel-button {
    -webkit-appearance: none;
    appearance: none;
}

.dt-meta {
    font-size: 0.72rem;
    color: var(--muted);
    white-space: nowrap;
}

/* ---- table ---- */
.dt-scroll {
    overflow-x: auto;
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    background: var(--surface);
}

.dt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.dt-table thead th {
    position: sticky;
    top: 0;
    padding: 0.7rem 1rem;
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
    user-select: none;
}

.dt-table thead th.is-sortable {
    cursor: pointer;
    transition: color 0.15s ease;
}
.dt-table thead th.is-sortable:hover {
    color: var(--foreground);
}

.th-inner {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.th-sort {
    display: inline-flex;
    flex-direction: column;
    line-height: 0;
    margin-top: -2px;
}
.sort-icon {
    color: color-mix(in oklab, var(--muted) 45%, transparent);
    transition: color 0.15s ease;
}
.sort-icon.is-active {
    color: var(--primary);
}

.dt-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border);
    color: var(--foreground);
    vertical-align: middle;
}
.dt-table tbody tr:last-child td {
    border-bottom: none;
}
.dt-table tbody tr:hover td {
    background: color-mix(in oklab, var(--primary-soft) 35%, transparent);
}

.dt-empty {
    padding: 2.5rem 1rem !important;
    text-align: center;
    color: var(--muted);
}

.text-right {
    text-align: right;
}
.text-center {
    text-align: center;
}
.text-left {
    text-align: left;
}

/* ---- pagination ---- */
.dt-pagination {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 0.3rem;
}

.dt-page-btn,
.dt-page-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    height: 2rem;
    padding: 0 0.5rem;
    border-radius: 0.5rem;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--foreground);
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}
.dt-page-btn:hover:not(:disabled),
.dt-page-num:hover:not(:disabled):not(.is-ellipsis) {
    border-color: var(--primary);
    color: var(--primary);
}
.dt-page-btn:disabled,
.dt-page-num:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.dt-page-num.is-active {
    background: var(--primary);
    border-color: var(--primary);
    color: var(--primary-foreground);
}
.dt-page-num.is-ellipsis {
    border: none;
    background: none;
    cursor: default;
}

@media (max-width: 640px) {
    .dt-search {
        min-width: 100%;
        max-width: 100%;
    }
}
</style>
