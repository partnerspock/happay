<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Happay POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 font-sans antialiased text-slate-100 min-h-screen flex items-center justify-center p-4" style="-webkit-overflow-scrolling: touch;">

    <div class="w-full max-w-md bg-slate-800 border border-slate-700/60 rounded-2xl shadow-2xl p-6 md:p-8">
        
        <!-- Brand / Header -->
        <div class="text-center mb-6 md:mb-8">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center font-bold text-2xl text-white mx-auto shadow-lg shadow-indigo-500/30 mb-3">
                H
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Happay POS</h1>
            <p class="text-xs text-slate-400 mt-1">Masuk untuk mengakses Terminal POS & Manajemen Store</p>
        </div>

        <!-- Error Flash Message -->
        @if ($errors->any())
            <div class="mb-6 p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ url('/login') }}" method="POST" class="space-y-4 md:space-y-5">
            @csrf

            <div>
                <label for="admin_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Nama Admin / Kasir</label>
                <input type="text" 
                       name="admin_name" 
                       id="admin_name" 
                       value="{{ old('admin_name') }}"
                       required 
                       autofocus
                       placeholder="Masukkan nama admin..."
                       class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base md:text-sm transition">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <input type="password" 
                       name="password" 
                       id="password" 
                       required 
                       placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-base md:text-sm transition">
            </div>

            <button type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3.5 md:py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/30 transition duration-200 text-xs md:text-sm mt-2">
                Login
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-700/50 text-center text-xs text-slate-500 font-mono">
            Happay POS &copy; {{ date('Y') }}
        </div>
    </div>

</body>
</html>