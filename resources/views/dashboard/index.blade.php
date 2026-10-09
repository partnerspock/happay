@extends('layouts.app')

@section('title', 'Dashboard - Happay POS')

@section('content')
<div class="p-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-1">Selamat datang kembali, <span class="font-semibold text-indigo-600">{{ auth()->user()->admin_name ?? 'Admin' }}</span>!</p>
    </div>

    <!-- Placeholder Card / Layout Kosong -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center shadow-sm">
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-3 font-bold text-xl">
            H
        </div>
        <h3 class="text-base font-semibold text-slate-800">Sistem POS Siap Digunakan</h3>
        <p class="text-sm text-slate-500 max-w-md mx-auto mt-1">
            Modul statistik & widget laporan akan ditampilkan di sini. Kamu bisa langsung menguji transaksi melalui menu <strong class="text-slate-700">Terminal POS</strong> di sidebar.
        </p>
    </div>
</div>
@endsection