<script setup>
import { onBeforeUnmount, onMounted, ref, nextTick } from "vue";
import { Bell } from "@lucide/vue";

const open = ref(false);
const notifications = ref([]);
const unread = ref(0);
const dropdownRef = ref(null);
let pollHandle = null;

const handleClickOutside = (event) => {
    if (open.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        open.value = false;
    }
};

async function fetchNotifications() {
    try {
        const res = await fetch(route("notifications.index"), {
            headers: { Accept: "application/json" },
        });
        
        // Hentikan penembakan jika sesi sudah mati/unauthorized
        if (res.status === 401 || res.status === 419 || res.status === 403) {
            if (pollHandle) clearInterval(pollHandle);
            return;
        }

        if (!res.ok) return;
        const json = await res.json();
        notifications.value = json.notifications ?? [];
        unread.value = json.unread ?? 0;
    } catch (_) {
        // ignore
    }
}

async function markRead() {
    try {
        await fetch(route("notifications.markRead"), {
            method: "POST",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content ?? "",
            },
            credentials: "same-origin",
        });
        unread.value = 0;
    } catch (_) {
        // ignore
    }
}

function toggle() {
    open.value = !open.value;
    if (open.value && unread.value > 0) {
        markRead();
    }
}

onMounted(() => {
    fetchNotifications();
    pollHandle = setInterval(fetchNotifications, 30000);
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    if (pollHandle) clearInterval(pollHandle);
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <button
            type="button"
            class="relative inline-flex h-9 w-9 items-center justify-center rounded-md text-[var(--muted)] hover:bg-[var(--primary-soft)] hover:text-[var(--primary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--ring)]/30"
            aria-label="Notifikasi"
            @click="toggle"
        >
            <Bell :size="18" />
            <span
                v-if="unread > 0"
                class="absolute -right-1 -top-1 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-medium text-white"
            >
                {{ unread > 9 ? "9+" : unread }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 top-full z-40 mt-2 w-80 rounded-md border border-[var(--border)] bg-[var(--surface)] shadow-[var(--shadow-card)]"
            role="dialog"
        >
            <div class="border-b border-[var(--border)] px-3 py-2 text-xs uppercase tracking-wide text-[var(--muted)]">
                Notifikasi
            </div>
            <ul class="max-h-96 overflow-y-auto divide-y divide-[var(--border)]">
                <li v-if="!notifications.length" class="px-3 py-6 text-center text-sm text-[var(--muted)]">
                    Belum ada notifikasi.
                </li>
                <li v-for="n in notifications" :key="n.id" class="px-3 py-3">
                    <p class="text-sm" :class="{ 'font-medium': !n.read_at }">
                        {{ n.data?.message ?? n.type }}
                    </p>
                    <p class="mt-1 text-[10px] uppercase tracking-wide text-[var(--muted)]">
                        {{ new Date(n.created_at).toLocaleString("id-ID") }}
                    </p>
                </li>
            </ul>
        </div>
    </div>
</template>