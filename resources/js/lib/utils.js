import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

/**
 * Merge Tailwind class strings with conflict resolution.
 * @param  {...any} inputs
 * @returns {string}
 */
export function cn(...inputs) {
    return twMerge(clsx(inputs));
}
