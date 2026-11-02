@extends('layouts.app')

@section('title', 'Transaction Report - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory, transfer, warehouse, history, audit, stocks, items & pos standard */
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
<div class="w-full h-full overflow-hidden bg-slate-100 app-content-zoom">
    <div class="h-full overflow-y-auto custom-scrollbar" style="-webkit-overflow-scrolling: touch;">

        {{-- ===== PAGE SHELL ===== --}}
        <div class="p-3.5 sm:p-5 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-20 sm:pb-12">

            {{-- 1. TOP HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                Transaction Report
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Laporan lengkap seluruh transaksi POS berdasarkan <span class="text-indigo-600 font-semibold">rentang tanggal dan cabang</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <a href="{{ route('report.transactions.export.csv') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 active:scale-95 text-white px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-lg shadow-emerald-600/25 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            {{-- 2. FILTER BAR COMPONENT (5-col standard) --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form action="{{ route('report.transactions') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                        
                        {{-- Warehouse Filter --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Warehouse
                            </label>
                            <select name="warehouse_id" 
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition {{ !empty($lockedWarehouseId) ? 'opacity-60 cursor-not-allowed' : '' }}"
                                    {{ !empty($lockedWarehouseId) ? 'disabled' : '' }}>
                                <option value="">Semua Warehouse</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ $selectedWarehouse == $wh->id ? 'selected' : '' }}>
                                        [{{ optional($wh->region)->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(!empty($lockedWarehouseId))
                                <input type="hidden" name="warehouse_id" value="{{ $lockedWarehouseId }}">
                            @endif
                        </div>

                        {{-- Tanggal Mulai --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Tanggal Mulai
                            </label>
                            <input type="date" name="start_date" value="{{ $startDate }}" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        </div>

                        {{-- Tanggal Selesai --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Tanggal Selesai
                            </label>
                            <input type="date" name="end_date" value="{{ $endDate }}" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        </div>

                        {{-- Cari Transaksi --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Cari Transaksi
                            </label>
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
                                </svg>
                                <input type="text" name="search" value="{{ $search }}" placeholder="No Invoice / Nama Pelanggan..." 
                                       class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                            </div>
                        </div>

                        {{-- Filter & Reset Buttons --}}
                        <div class="flex items-center gap-1.5">
                            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white h-[42px] px-3 rounded-xl font-bold text-xs transition flex items-center justify-center shadow-sm">
                                <span>Filter Data</span>
                            </button>
                            <a href="{{ route('report.transactions') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition active:scale-95" title="Reset Filter">
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
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/60">
                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Menampilkan Seluruh Data Transaksi</span>
                    <span class="text-xs font-mono font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-xl border border-indigo-100">
                        Total: {{ count($transactions) }} Transaksi
                    </span>
                </div>
                
                {{-- A. DESKTOP TABLE VIEW --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                            <tr>
                                <th class="py-3.5 px-4">Invoice UUID</th>
                                <th class="py-3.5 px-4">Waktu</th>
                                <th class="py-3.5 px-4">Pelanggan</th>
                                <th class="py-3.5 px-4">Warehouse</th>
                                <th class="py-3.5 px-3 text-center">Total Item</th>
                                <th class="py-3.5 px-4 text-right">Nominal (Rp)</th>
                                <th class="py-3.5 px-3 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($transactions as $trx)
                            <tr class="hover:bg-indigo-50/25 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 break-all">
                                    {{ $trx->uuid }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">
                                    {{ $trx->created_at ? $trx->created_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $trx->transaction_info_name ?? 'Guest' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $trx->transaction_info_number }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    {{ optional($trx->warehouse)->warehouse_name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-bold font-mono">
                                    {{ $trx->items->sum('transaction_item_qty') }} pcs
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($trx->transaction_pricetotal, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($trx->transaction_status === 'completed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                            Completed
                                        </span>
                                    @elseif($trx->transaction_status === 'draft')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                                            Draft
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                            {{ ucfirst($trx->transaction_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('pos.print', $trx->id) }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition text-[11px]">
                                        <span>🖨️ Print Struk</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Tidak ada data transaksi ditemukan dalam rentang tanggal tersebut.</p>
                                        <p class="text-slate-400 text-xs">Sesuaikan filter tanggal atau warehouse untuk menampilkan laporan.</p>
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
                            <span class="font-mono font-bold text-indigo-600 text-xs break-all">{{ $trx->uuid }}</span>
                            @if($trx->transaction_status === 'completed')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">Completed</span>
                            @elseif($trx->transaction_status === 'draft')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">Draft</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 shrink-0">{{ ucfirst($trx->transaction_status) }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-xs">
                            <div class="min-w-0">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Pelanggan</span>
                                <span class="font-bold text-slate-900 truncate block">{{ $trx->transaction_info_name ?? 'Guest' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono truncate block">{{ $trx->transaction_info_number ?? '-' }}</span>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Warehouse</span>
                                <span class="font-bold text-slate-800 truncate block">{{ optional($trx->warehouse)->warehouse_name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono truncate block">{{ $trx->created_at ? $trx->created_at->format('d M Y, H:i') : '-' }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-slate-50/70 rounded-xl p-2.5 border border-slate-100 text-xs font-mono">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Total Item</span>
                                <span class="font-bold text-slate-700">{{ $trx->items->sum('transaction_item_qty') }} pcs</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Nominal</span>
                                <span class="font-extrabold text-slate-900">Rp {{ number_format($trx->transaction_pricetotal, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="pt-1">
                            <a href="{{ route('pos.print', $trx->id) }}" target="_blank" rel="noopener noreferrer"
                               class="w-full bg-slate-100 active:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                                <span>🖨️ Print Struk</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Tidak ada data transaksi ditemukan dalam rentang tanggal tersebut.
                    </div>
                    @endforelse
                </div>

            </div>

        </div>

    </div>
</div>
@endsection