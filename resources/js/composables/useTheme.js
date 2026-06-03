import { ref, onMounted } from "vue";

/**
 * Shared light/dark theme control.
 * The initial theme is bootstrapped pre-paint in app.js (window.__setTheme),
 * this composable keeps a reactive mirror and exposes a toggle.
 */
const isDark = ref(true);

export function useTheme() {
    function syncFromDom() {
        isDark.value = document.documentElement.classList.contains("dark");
    }

    function setTheme(mode) {
        const dark = mode === "dark";
        isDark.value = dark;
        if (typeof window !== "undefined" && window.__setTheme) {
            window.__setTheme(dark ? "dark" : "light");
        } else if (typeof document !== "undefined") {
            document.documentElement.classList.toggle("dark", dark);
            try {
                localStorage.setItem("trimexas-theme", dark ? "dark" : "light");
            } catch (_) {
                /* ignore */
            }
        }
    }

    function toggleTheme() {
        setTheme(isDark.value ? "light" : "dark");
    }

    onMounted(syncFromDom);

    return { isDark, toggleTheme, setTheme };
}
