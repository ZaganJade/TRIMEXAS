<script setup>
/**
 * LandingMascot — cute kawaii 2D stickers for the landing page.
 * Pure inline SVG, themed via CSS variables so they recolor with
 * the dark/light toggle. Wrap in <motion.div> for float/hover motion.
 */
defineProps({
    name: { type: String, default: "scholar" }, // scholar | star | gem | bot | book
    size: { type: [Number, String], default: 76 },
});
</script>

<template>
    <!-- SCHOLAR — graduation-cap blob -->
    <svg v-if="name === 'scholar'" :width="size" :height="size" viewBox="0 0 72 72" fill="none" aria-hidden="true">
        <ellipse cx="36" cy="64" rx="16" ry="3" fill="var(--ink)" opacity="0.12" />
        <rect x="15" y="24" width="42" height="36" rx="17" fill="var(--primary)" />
        <rect x="15" y="24" width="42" height="36" rx="17" fill="url(#mShine)" opacity="0.25" />
        <polygon points="36,7 62,18 36,29 10,18" fill="var(--ink)" />
        <rect x="34" y="24" width="4" height="7" fill="var(--ink)" />
        <path d="M60 18 v9" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" />
        <circle cx="60" cy="29" r="3" fill="var(--accent)" />
        <circle cx="29" cy="42" r="3.4" fill="#fff" />
        <circle cx="43" cy="42" r="3.4" fill="#fff" />
        <circle cx="29.8" cy="42.6" r="1.5" fill="#0b1220" />
        <circle cx="43.8" cy="42.6" r="1.5" fill="#0b1220" />
        <path d="M31 50 q5 5 10 0" stroke="#fff" stroke-width="2.2" fill="none" stroke-linecap="round" />
        <ellipse cx="23.5" cy="48" rx="3" ry="2" fill="#fff" opacity="0.45" />
        <ellipse cx="48.5" cy="48" rx="3" ry="2" fill="#fff" opacity="0.45" />
        <defs>
            <linearGradient id="mShine" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#fff" />
                <stop offset="1" stop-color="#fff" stop-opacity="0" />
            </linearGradient>
        </defs>
    </svg>

    <!-- STAR — achievement -->
    <svg v-else-if="name === 'star'" :width="size" :height="size" viewBox="0 0 72 72" fill="none" aria-hidden="true">
        <path
            d="M36 6c1.4 0 2.6.8 3.2 2l6.2 12.6 13.9 2c1.4.2 2.6 1.2 3 2.6.4 1.4 0 2.9-1 3.9l-10 9.8 2.4 13.8c.2 1.4-.3 2.8-1.5 3.6-1.2.8-2.7.9-4 .3L36 52.6l-12.4 6.5c-1.3.6-2.8.5-4-.3-1.2-.8-1.7-2.2-1.5-3.6l2.4-13.8-10-9.8c-1-1-1.4-2.5-1-3.9.4-1.4 1.6-2.4 3-2.6l13.9-2L32.8 8c.6-1.2 1.8-2 3.2-2Z"
            fill="var(--accent)"
        />
        <circle cx="30" cy="34" r="2.8" fill="#0b1220" />
        <circle cx="42" cy="34" r="2.8" fill="#0b1220" />
        <circle cx="31" cy="33.2" r="0.9" fill="#fff" />
        <circle cx="43" cy="33.2" r="0.9" fill="#fff" />
        <path d="M32 40 q4 4 8 0" stroke="#0b1220" stroke-width="2.2" fill="none" stroke-linecap="round" />
        <ellipse cx="25.5" cy="39" rx="2.6" ry="1.7" fill="#fff" opacity="0.5" />
        <ellipse cx="46.5" cy="39" rx="2.6" ry="1.7" fill="#fff" opacity="0.5" />
    </svg>

    <!-- GEM — data/score -->
    <svg v-else-if="name === 'gem'" :width="size" :height="size" viewBox="0 0 72 72" fill="none" aria-hidden="true">
        <ellipse cx="36" cy="66" rx="13" ry="2.6" fill="var(--ink)" opacity="0.12" />
        <polygon points="22,12 50,12 62,28 36,64 10,28" fill="var(--accent-2)" />
        <polygon points="22,12 50,12 62,28 10,28" fill="#fff" opacity="0.16" />
        <path d="M10 28 L36 64 L62 28" stroke="#fff" stroke-width="1" opacity="0.3" fill="none" />
        <line x1="22" y1="12" x2="29" y2="28" stroke="#fff" stroke-width="1" opacity="0.3" />
        <line x1="50" y1="12" x2="43" y2="28" stroke="#fff" stroke-width="1" opacity="0.3" />
        <circle cx="30" cy="34" r="2.6" fill="#fff" />
        <circle cx="42" cy="34" r="2.6" fill="#fff" />
        <path d="M32 39 q4 3 8 0" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" />
    </svg>

    <!-- BOT — AI -->
    <svg v-else-if="name === 'bot'" :width="size" :height="size" viewBox="0 0 72 72" fill="none" aria-hidden="true">
        <line x1="36" y1="6" x2="36" y2="16" stroke="var(--accent)" stroke-width="2.4" stroke-linecap="round" />
        <circle cx="36" cy="6" r="3.4" fill="var(--accent)" />
        <rect x="13" y="28" width="5" height="12" rx="2.5" fill="var(--accent)" />
        <rect x="54" y="28" width="5" height="12" rx="2.5" fill="var(--accent)" />
        <rect x="16" y="16" width="40" height="36" rx="13" fill="var(--primary)" />
        <rect x="22" y="24" width="28" height="18" rx="9" fill="#0b1220" opacity="0.9" />
        <circle cx="29" cy="33" r="3.6" fill="#fff" />
        <circle cx="43" cy="33" r="3.6" fill="var(--accent)" />
        <circle cx="29" cy="33" r="1.5" fill="#0b1220" />
        <rect x="30" y="46" width="12" height="3" rx="1.5" fill="#fff" opacity="0.7" />
    </svg>

    <!-- BOOK — knowledge -->
    <svg v-else :width="size" :height="size" viewBox="0 0 72 72" fill="none" aria-hidden="true">
        <ellipse cx="36" cy="62" rx="20" ry="3" fill="var(--ink)" opacity="0.12" />
        <path d="M36 22 C28 16 18 16 12 18 v34 c6 -2 16 -2 24 4 z" fill="var(--primary)" />
        <path d="M36 22 C44 16 54 16 60 18 v34 c-6 -2 -16 -2 -24 4 z" fill="var(--primary-dark)" />
        <path d="M36 22 v26" stroke="#fff" stroke-width="1.4" opacity="0.5" />
        <circle cx="26" cy="32" r="2.6" fill="#fff" />
        <circle cx="46" cy="32" r="2.6" fill="#fff" />
        <path d="M30 38 q6 4 12 0" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" />
        <line x1="17" y1="28" x2="29" y2="29" stroke="#fff" stroke-width="1.4" opacity="0.4" stroke-linecap="round" />
        <line x1="43" y1="29" x2="55" y2="28" stroke="#fff" stroke-width="1.4" opacity="0.4" stroke-linecap="round" />
    </svg>
</template>
