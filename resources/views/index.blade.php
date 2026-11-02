<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Happay POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Touch friendly scrollbars & smooth rendering */
        body {
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body class="bg-slate-950 font-sans antialiased text-slate-100 min-h-screen flex items-center justify-center p-4 selection:bg-indigo-600 selection:text-white">

    <div class="w-full max-w-md bg-slate-900/90 border border-slate-800 rounded-3xl shadow-2xl p-6 sm:p-8 backdrop-blur-xl relative overflow-hidden">
        
        {{-- Subtle decorative glow background --}}
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Brand / Header -->
        <div class="text-center mb-6 sm:mb-8 relative z-10">
            <div class="w-14 h-14 bg-gradient-to-tr from-indigo-600 to-indigo-500 rounded-2xl flex items-center justify-center font-black text-2xl text-white mx-auto shadow-lg shadow-indigo-600/30 mb-3.5 border border-indigo-400/20">
                H
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Happay POS</h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Masuk untuk mengakses Terminal POS &amp; Manajemen Store</p>
        </div>

        <!-- Error Flash Message -->
        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-rose-400 text-xs space-y-1.5 relative z-10 shadow-sm animate-in fade-in">
                <div class="font-bold flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <span>Gagal Masuk</span>
                </div>
                @foreach ($errors->all() as $error)
                    <p class="pl-5">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ url('/login') }}" method="POST" class="space-y-4 sm:space-y-5 relative z-10">
            @csrf

            <div>
                <div class="relative">
    <div class="flex items-center gap-1 mb-1.5">
        <label for="admin_name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            Nama Admin / Kasir
        </label>
        <button type="button" onclick="document.getElementById('dbInfoPopup').classList.toggle('hidden')" class="text-slate-500 hover:text-slate-300 transition focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>
    </div>
    <div id="dbInfoPopup" class="hidden absolute z-10 w-64 p-3 text-xs bg-slate-900 border border-slate-700 rounded-lg shadow-lg text-slate-300">
        <div class="font-semibold text-white mb-1">Default User Info</div>
        <code class="block bg-slate-800 p-1 rounded text-cyan-400 break-all">Happay/Database/HappayDatabaseSeeder.php</code>
    </div>
</div>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                    <input type="text" 
                           name="admin_name" 
                           id="admin_name" 
                           value="{{ old('admin_name') }}"
                           required 
                           autofocus
                           placeholder="Masukkan nama admin..."
                           class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm font-medium transition">
                </div>
            </div>

            <div>
                <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Password</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                    </svg>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required 
                           placeholder="••••••••"
                           class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs sm:text-sm font-medium transition">
                </div>
            </div>

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 active:scale-95 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/25 transition-all duration-200 text-xs sm:text-sm mt-2 flex items-center justify-center gap-2">
                <span>Login ke Terminal</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-800 text-center text-xs text-slate-500 font-mono relative z-10">
            Happay POS &copy; {{ date('Y') }}
        </div>
    </div>

</body>
</html>