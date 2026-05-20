<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#3189C6">
        <meta name="description" content="Trimexas — Sistem Pendukung Keputusan Beasiswa Triv Foundation × MEXC Foundation berbasis metode Fuzzy Tsukamoto.">
        <meta name="color-scheme" content="light dark">

        <meta property="og:title" content="Trimexas — SPK Beasiswa Fuzzy Tsukamoto">
        <meta property="og:description" content="Penilaian beasiswa tanpa tebak-tebakan: angka, jejak, dan keputusan yang bisa dipertanggungjawabkan.">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary">

        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%233189C6'%3E%3Cpath d='M3 18 L8 6 L13 14 L17 8 L21 18' stroke='%233189C6' stroke-width='2.4' stroke-linecap='round' fill='none'/%3E%3C/svg%3E">

        <title inertia>{{ config('app.name', 'Trimexas') }}</title>

        @routes

        {{-- Inline theme bootstrap to avoid flash-of-wrong-theme --}}
        <script>
            (function () {
                try {
                    var stored = localStorage.getItem('trimexas-theme');
                    var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var initial = stored || (prefersDark ? 'dark' : 'light');
                    document.documentElement.classList.toggle('dark', initial === 'dark');
                } catch (e) {}
            })();
        </script>

        {{-- Bunny Fonts: Inter (body) + Space Grotesk (display) --}}
        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="bg-[var(--background)] text-[var(--foreground)] antialiased">
        @inertia
    </body>
</html>
