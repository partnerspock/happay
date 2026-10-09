<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Happay POS System')</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    /* 100dvh = tinggi layar sebenarnya di HP (tidak terpotong address bar Safari/Chrome) */
    html, body { height: 100%; }
    body { height: 100vh; height: 100dvh; }
    [x-cloak] { display: none !important; }
</style>
    @stack('styles')
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 overflow-hidden">

<!-- Wrapper Utama: mobileMenu dipakai bersama oleh top bar HP & sidebar -->
<div x-data="{ mobileMenu: false }" class="flex h-full w-full overflow-hidden">

<!-- Sidebar Navigation -->
        @include('layouts.partials.sidebar')

<!-- Main Content Area -->
<main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden bg-slate-50 relative">

<!-- Top bar HP (hanya tampil < 768px) -->
<header class="md:hidden shrink-0 h-12 bg-slate-900 text-white flex items-center gap-3 px-3 border-b border-slate-800">
    <button type="button" @click="mobileMenu = true" class="p-1.5 -ml-1 rounded-lg hover:bg-slate-800 focus:outline-none" aria-label="Buka menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0">H</div>
    <span class="font-bold text-sm tracking-wide">Happay</span>
</header>

<!-- Flash Message Alert Component -->
            @if(session('success'))
<div class="m-3 md:m-6 mb-0 p-3 md:p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center justify-between shrink-0">
<span>{{ session('success') }}</span>
<button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
</div>
            @endif

            @if(session('error'))
<div class="m-3 md:m-6 mb-0 p-3 md:p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm flex items-center justify-between shrink-0">
<span>{{ session('error') }}</span>
<button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
</div>
            @endif

<!-- Dynamic View Content (min-h-0 + flex-1 supaya mengisi sisa tinggi setelah top bar) -->
<div class="flex-1 min-h-0 overflow-hidden">
                @yield('content')
</div>
</main>
</div>

    @stack('scripts')
</body>
</html>