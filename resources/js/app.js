import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { ZiggyVue } from "ziggy-js";

const appName = import.meta.env.VITE_APP_NAME || "Trimexas";

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: false });
        return pages[`./Pages/${name}.vue`]();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "#3189C6",
        showSpinner: false,
    },
});

// Theme bootstrap (no flash) — must run before paint.
// We also expose a tiny helper for the toggle.
const stored = localStorage.getItem("trimexas-theme");
const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
const initial = stored ?? (prefersDark ? "dark" : "light");
document.documentElement.classList.toggle("dark", initial === "dark");
window.__setTheme = (mode) => {
    localStorage.setItem("trimexas-theme", mode);
    document.documentElement.classList.toggle("dark", mode === "dark");
};
