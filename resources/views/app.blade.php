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

        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%233189C6'/%3E%3Cpath d='M5 17 L10 7 L13 13 L19 5' stroke='%23F7FAFC' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3C/svg%3E">

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

        {{-- PRD §11.3: Space Grotesk (heading) + Inter (body) + JetBrains Mono (numerics/ticker) --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="bg-[var(--background)] text-[var(--foreground)] antialiased">
        @inertia
    </body>
</html>
