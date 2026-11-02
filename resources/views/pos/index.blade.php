@extends('layouts.app')

@section('title', 'POS Transactions - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory, transfer, warehouse, history, audit, stocks, items & report standard */
    @media (min-width: 1280px) {
        .app-content-zoom {
            zoom: 0.9;
        }
    }
    /* Touch friendly scrollbars */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(241, 245, 249, 0.6);
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(203, 213, 225, 0.8);
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 1);
    }
</style>
@endpush

@section('content')
@php
    $statusClass = [
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'draft'     => 'bg-amber-50 text-amber-700 border-amber-200',
        'void'      => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
    $statusLabel = ['completed' => 'Completed', 'draft' => 'Draft', 'void' => 'Void'];
@endphp

<div class="w-full h-full overflow-hidden bg-slate-100 app-content-zoom" x-data="posIndexData()">
    <div class="h-full overflow-y-auto custom-scrollbar" style="-webkit-overflow-scrolling: touch;">

        {{-- ===== PAGE SHELL ===== --}}
        <div class="p-3.5 sm:p-5 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-20 sm:pb-12">

            {{-- 1. TOP HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.75 2.25M4.5 3.75H20.25a2.25 2.25 0 0 1 2.25 2.25v13.5a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25V6a2.25 2.25 0 0 1 2.25-2.25Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                POS Transactions
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Kelola transaksi penjualan, kasir, dan <span class="text-indigo-600 font-semibold">riwayat pernominalan</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <a href="{{ route('pos.create') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 active:scale-95 text-white px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-lg shadow-indigo-600/25 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Buka Kasir (POS)</span>
                    </a>
                </div>
            </div>

            {{-- FLASH ALERTS --}}
            @if(session('success'))
            <div class="flex items-start gap-3 rounded-2xl bg-emerald-50 border border-emerald-200/90 p-4 text-emerald-800 text-xs sm:text-sm font-medium shadow-sm animate-in fade-in">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <span class="font-bold">Berhasil!</span> {{ session('success') }}
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="flex items-start gap-3 rounded-2xl bg-rose-50 border border-rose-200/90 p-4 text-rose-800 text-xs sm:text-sm font-medium shadow-sm animate-in fade-in">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <span class="font-bold">Terjadi Kesalahan!</span> {{ session('error') }}
                </div>
            </div>
            @endif

            {{-- 2. FILTER & SEARCH COMPONENT --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form action="{{ route('pos.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Cari Transaksi
                            </label>
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
                                </svg>
                                <input type="text" name="search" value="{{ $search }}" placeholder="No Invoice / Nama Customer..."
                                       class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Warehouse
                            </label>
                            <select name="warehouse_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="">Semua Warehouse</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ $selectedWarehouse == $wh->id ? 'selected' : '' }}>
                                        [{{ optional($wh->region)->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Status
                            </label>
                            <select name="transaction_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="">Semua Status</option>
                                <option value="completed" {{ $selectedStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="draft" {{ $selectedStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="void" {{ $selectedStatus === 'void' ? 'selected' : '' }}>Void</option>
                                <option value="cancel" {{ $selectedStatus === 'cancel' ? 'selected' : '' }}>Cancel</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white h-[42px] px-3 rounded-xl font-bold text-xs transition flex items-center justify-center shadow-sm">
                                <span>Filter Data</span>
                            </button>
                            <a href="{{ route('pos.index') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition active:scale-95" title="Reset Filter">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </a>
                        </div>

                    </div>
                </form>
            </div>

            {{-- 3. MAIN DATA VIEW --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex justify-between items-center gap-2 bg-slate-50/60">
                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Daftar Transaksi POS</span>
                    <span class="text-xs font-mono font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-xl border border-indigo-100 whitespace-nowrap">
                        Total: {{ $transactions->total() }} Data
                    </span>
                </div>

                {{-- A. DESKTOP TABLE VIEW --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                            <tr>
                                <th class="py-3.5 px-4">Invoice UUID</th>
                                <th class="py-3.5 px-4">Warehouse &amp; Kasir</th>
                                <th class="py-3.5 px-4">Customer</th>
                                <th class="py-3.5 px-4 text-right">Total Price</th>
                                <th class="py-3.5 px-4 text-right">Margin</th>
                                <th class="py-3.5 px-3 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($transactions as $trx)
                            <tr class="hover:bg-indigo-50/25 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600">
                                    <span class="break-all">{{ $trx->uuid }}</span>
                                    <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $trx->created_at ? $trx->created_at->format('d M Y, H:i') : '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">[{{ optional(optional($trx->warehouse)->region)->region_alias ?? 'N/A' }}] {{ optional($trx->warehouse)->warehouse_name ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Kasir: {{ optional($trx->cashier)->admin_name ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800">{{ $trx->transaction_info_name ?? 'Walk-in' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $trx->transaction_info_number ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format(($trx->transaction_pricetotal + $trx->transaction_tax), 0, ',', '.') }}
                                    <div class="text-[9px] text-slate-400 font-normal">Tax: Rp {{ number_format($trx->transaction_tax, 0, ',', '.') }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-semibold text-emerald-600 whitespace-nowrap">
                                    Rp {{ number_format($trx->transaction_margin, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] border {{ $statusClass[$trx->transaction_status] ?? 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                        {{ $statusLabel[$trx->transaction_status] ?? 'Cancel' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('pos.print', $trx->id) }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg transition text-[11px]">
                                            <span>🖨️ Struk</span>
                                        </a>

                                        @if($trx->transaction_status === 'draft')
                                            <a href="{{ route('pos.create', ['continue' => $trx->id]) }}" 
                                               class="inline-flex items-center gap-1 font-bold text-amber-900 bg-amber-500 hover:bg-amber-600 px-3 py-1.5 rounded-lg transition text-[11px] shadow-sm">
                                                <span>⚠️ Continue</span>
                                            </a>
                                        @elseif($trx->transaction_status === 'completed')
                                            <button type="button" @click="openVoidModal('{{ $trx->id }}', '{{ $trx->uuid }}')" 
                                                    class="inline-flex items-center gap-1 font-bold text-rose-800 bg-rose-100/80 hover:bg-rose-200 px-2.5 py-1.5 rounded-lg transition text-[11px] shadow-sm">
                                                Void
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.75 2.25M4.5 3.75H20.25a2.25 2.25 0 0 1 2.25 2.25v13.5a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25V6a2.25 2.25 0 0 1 2.25-2.25Z"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Belum ada riwayat transaksi POS.</p>
                                        <p class="text-slate-400 text-xs">Silakan lakukan penjualan kasir baru atau ubah filter pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-mono font-bold text-indigo-600 text-xs break-all">{{ $trx->uuid }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $trx->created_at ? $trx->created_at->format('d M Y, H:i') : '-' }}</div>
                            </div>
                            <span class="border px-2.5 py-0.5 rounded-full font-bold text-[10px] shrink-0 {{ $statusClass[$trx->transaction_status] ?? 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                {{ $statusLabel[$trx->transaction_status] ?? 'Cancel' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-xs">
                            <div class="min-w-0">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Warehouse</span>
                                <span class="font-bold text-slate-900 truncate block">[{{ optional(optional($trx->warehouse)->region)->region_alias ?? 'N/A' }}] {{ optional($trx->warehouse)->warehouse_name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 truncate block">Kasir: {{ optional($trx->cashier)->admin_name ?? '-' }}</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Customer</span>
                                <span class="font-bold text-slate-800 truncate block">{{ $trx->transaction_info_name ?? 'Walk-in' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono truncate block">{{ $trx->transaction_info_number ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-slate-50/70 rounded-xl p-2.5 border border-slate-100 text-xs font-mono">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Total</span>
                                <span class="font-extrabold text-slate-900">Rp {{ number_format(($trx->transaction_pricetotal + $trx->transaction_tax), 0, ',', '.') }}</span>
                                <div class="text-[9px] text-slate-400 font-sans">Tax: Rp {{ number_format($trx->transaction_tax, 0, ',', '.') }}</div>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Margin</span>
                                <span class="font-bold text-emerald-600">Rp {{ number_format($trx->transaction_margin, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Aksi Tombol HP -->
                        <div class="flex gap-2 pt-1">
                            <a href="{{ route('pos.print', $trx->id) }}" target="_blank" class="flex-1 text-center bg-slate-100 active:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-bold text-xs">
                                🖨️ Struk
                            </a>

                            @if($trx->transaction_status === 'draft')
                                <a href="{{ route('pos.create', ['continue' => $trx->id]) }}" class="flex-1 text-center bg-amber-500 active:bg-amber-600 text-amber-950 py-2.5 rounded-xl font-bold text-xs shadow-sm">
                                    ⚠️ Continue
                                </a>
                            @elseif($trx->transaction_status === 'completed')
                                <button type="button" @click="openVoidModal('{{ $trx->id }}', '{{ $trx->uuid }}')" class="flex-1 bg-rose-600 active:bg-rose-700 text-white py-2.5 rounded-xl font-bold text-xs shadow-sm">
                                    Void
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Belum ada riwayat transaksi POS.
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if(method_exists($transactions, 'hasPages') && $transactions->hasPages())
                <div class="p-3.5 sm:p-4 border-t border-slate-100 bg-slate-50/60 overflow-x-auto">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>

        </div>

        {{-- MODAL VOID OTORISASI SPV / MANAGER --}}
        <div x-cloak x-show="showVoidModal" x-transition.opacity
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4"
             @keydown.escape.window="showVoidModal = false">
            <div @click.outside="showVoidModal = false" 
                 class="w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl overflow-hidden max-h-[92dvh] sm:max-h-[90vh] flex flex-col border border-slate-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-rose-50/80 via-white to-slate-50 p-4 sm:p-5 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold shadow-md shadow-rose-600/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9-.03a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM12 12.75h.008v.008H12v-.008Z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Otorisasi Void Transaksi</h3>
                            <p class="text-[10px] sm:text-xs text-slate-400">Masukkan kredensial SPV/Manager untuk otorisasi void.</p>
                        </div>
                    </div>
                    <button type="button" @click="showVoidModal = false" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="'/pos/' + selectedTrxId + '/status'" method="POST" class="p-4 sm:p-6 space-y-4 text-xs overflow-y-auto flex-1 custom-scrollbar">
                    @csrf @method('PATCH')
                    <input type="hidden" name="transaction_status" value="void">

                    @if($errors->has('spv_password'))
                        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3.5 rounded-2xl font-bold flex items-start gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                            <span>{{ $errors->first('spv_password') }}</span>
                        </div>
                    @endif

                    <div class="bg-amber-50 border border-amber-200 text-amber-900 p-3.5 rounded-2xl flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9-.03a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM12 12.75h.008v.008H12v-.008Z"/></svg>
                        <div class="text-[11px] leading-relaxed">
                            Anda akan melakukan <b>Void</b> pada transaksi ini. Stok produk akan dikembalikan ke gudang secara otomatis.
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                            Nama Admin SPV / Manager <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="spv_username" value="{{ old('spv_username') }}" required placeholder="Masukkan nama admin SPV/Mgr..." autocomplete="off" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                            Password SPV / Manager <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="spv_password" required placeholder="••••••••" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition">
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-slate-100">
                        <button type="button" @click="showVoidModal = false" class="flex-1 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition text-xs">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-3 rounded-xl font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/25 transition text-xs active:scale-95">
                            Konfirmasi Void
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function posSystem() {
    return {
        products: [],
        cart: [],
        searchQuery: '',
        selectedWarehouseId: '',
        cartOpen: false,

        async initPos() {
            const whSelect = document.getElementById('active_warehouse_id');
            if (whSelect) {
                this.selectedWarehouseId = whSelect.value;
                // Tunggu sampai stok produk di warehouse tersebut selesai di-fetch
                await this.fetchStocks();

                // OTOMATIS LOAD ITEM DRAFT KE KERANJANG
                @if(isset($continueTransaction) &&$continueTransaction)
                    const draftItems = @json($continueTransaction->items);
                    draftItems.forEach(dItem => {
                        // Cocokan item_id dari draft dengan stock_master yang ada di katalog warehouse
                        const found = this.products.find(p => p.item_id == dItem.item_id);
                        if (found) {
                            this.cart.push({
                                stock_master_id: found.stock_master_id,
                                item_name: found.item_name,
                                item_variant: found.item_variant,
                                item_image: found.item_image,
                                price: dItem.transaction_item_priceperitem ?? found.item_price,
                                qty: dItem.transaction_item_qty,
                                max_stock: found.stock_now
                            });
                        }
                    });
                @endif
            }
        },

        fetchStocks() {
            const whSelect = document.getElementById('active_warehouse_id');
            if (whSelect) {
                this.selectedWarehouseId = whSelect.value;
            }
            if (!this.selectedWarehouseId) return Promise.resolve();

            return fetch(`/pos/stocks?warehouse_id=${this.selectedWarehouseId}`)
                .then(res => res.json())
                .then(data => {
                    this.products = data.map(p => ({ ...p, temp_qty: 1 }));
                });
        },

        get filteredProducts() {
            if (!this.searchQuery) return this.products;
            const q = this.searchQuery.toLowerCase();
            return this.products.filter(p =>
                p.item_name.toLowerCase().includes(q) ||
                p.item_sku.toLowerCase().includes(q)
            );
        },

        incrementQty(product) {
            if (product.temp_qty < product.stock_now) {
                product.temp_qty++;
            }
        },

        decrementQty(product) {
            if (product.temp_qty > 1) {
                product.temp_qty--;
            }
        },

        addToCart(product) {
            const qtyToAdd = product.temp_qty || 1;
            const existingIndex = this.cart.findIndex(item => item.stock_master_id === product.stock_master_id);

            if (existingIndex > -1) {
                const newQty = this.cart[existingIndex].qty + qtyToAdd;
                if (newQty <= product.stock_now) {
                    this.cart[existingIndex].qty = newQty;
                } else {
                    alert('Stok tidak mencukupi!');
                }
            } else {
                this.cart.push({
                    stock_master_id: product.stock_master_id,
                    item_name: product.item_name,
                    item_variant: product.item_variant,
                    item_image: product.item_image,
                    price: product.item_price,
                    qty: qtyToAdd,
                    max_stock: product.stock_now
                });
            }
            product.temp_qty = 1;
        },

        updateCartQty(index, change) {
            const item = this.cart[index];
            const newQty = item.qty + change;
            if (newQty > 0 && newQty <= item.max_stock) {
                item.qty = newQty;
            } else if (newQty <= 0) {
                this.removeFromCart(index);
            } else {
                alert('Stok maksimum tercapai!');
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            this.cart = [];
        },

        get cartCount() {
            return this.cart.reduce((sum, item) => sum + item.qty, 0);
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        get tax() {
            return this.subtotal * 0.11;
        },

        get grandTotal() {
            return this.subtotal + this.tax;
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }
    }
}
</script>
@endsection