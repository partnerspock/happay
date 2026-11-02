@extends('layouts.app')

@section('title', 'Item Report - Happay')

@push('styles')
<style>
    @media (min-width: 1280px) {
        .app-content-zoom {
            zoom: 0.9;
        }
    }
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

        <div class="p-3.5 sm:p-5 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-20 sm:pb-12">

            {{-- TOP HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5h18M3 9.75h18m-13.5-6.75h9m-9 15h9M3 18.75h18"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                Item Movement Report
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Laporan pergerakan stok masuk dan keluar (in/out) beserta <span class="text-emerald-600 font-semibold">referensi transaksi atau transfer</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <a href="{{ route('report.items.export.csv') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 active:scale-95 text-white px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-lg shadow-emerald-600/25 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            {{-- FILTER BAR COMPONENT (5-col standard) --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form action="{{ route('report.items') }}" method="GET">
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

                        {{-- Cari Item / SKU --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Cari Item / SKU
                            </label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Nama barang / SKU..." 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        </div>

                        {{-- Filter & Reset Buttons --}}
                        <div class="flex items-center gap-1.5">
                            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white h-[42px] px-3 rounded-xl font-bold text-xs transition flex items-center justify-center shadow-sm">
                                <span>Filter Data</span>
                            </button>
                            <a href="{{ route('report.items') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition active:scale-95" title="Reset Filter">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </a>
                        </div>

                    </div>
                </form>
            </div>

            {{-- MAIN DATA VIEW --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/60">
                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Riwayat Pergerakan Item</span>
                    <span class="text-xs font-mono font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-xl border border-indigo-100">
                        Total: {{ count($histories) }} Log
                    </span>
                </div>
                
                {{-- A. DESKTOP TABLE VIEW --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                            <tr>
                                <th class="py-3.5 px-4">Waktu</th>
                                <th class="py-3.5 px-4">Warehouse</th>
                                <th class="py-3.5 px-4">Nama Item &amp; SKU</th>
                                <th class="py-3.5 px-4">Referensi</th>
                                <th class="py-3.5 px-3 text-center">Tipe</th>
                                <th class="py-3.5 px-3 text-center">Qty Before</th>
                                <th class="py-3.5 px-3 text-center">Change</th>
                                <th class="py-3.5 px-3 text-center">Qty Now</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($histories as $h)
                            <tr class="hover:bg-indigo-50/25 transition">
                                <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">
                                    {{ $h->created_at ? $h->created_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    {{ optional($h->warehouse)->warehouse_name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ optional($h->item)->item_name ?? '-' }}</div>
                                    <div class="text-[10px] text-indigo-600 font-mono font-semibold mt-0.5">{{ optional($h->item)->item_sku ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600 text-[11px]">
                                    {{ $h->history_change_description ?? '-' }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($h->history_change__type === 'increased' || $h->history_change__type === 'in')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            IN
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200">
                                            OUT
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-600">
                                    {{ $h->history_before }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono font-bold {{ ($h->history_change__type === 'increased' || $h->history_change__type === 'in') ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ ($h->history_change__type === 'increased' || $h->history_change__type === 'in') ? '+' : '-' }}{{ $h->history_change }}
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono font-extrabold text-slate-900">
                                    {{ $h->history_now }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5h18M3 9.75h18m-13.5-6.75h9m-9 15h9M3 18.75h18"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Tidak ada riwayat pergerakan item ditemukan.</p>
                                        <p class="text-slate-400 text-xs">Sesuaikan rentang tanggal atau filter warehouse untuk menampilkan log.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($histories as $h)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-slate-400 text-[10px]">
                                {{ $h->created_at ? $h->created_at->format('d M Y, H:i') : '-' }}
                            </span>
                            @if($h->history_change__type === 'increased' || $h->history_change__type === 'in')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">IN (+)</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">OUT (-)</span>
                            @endif
                        </div>

                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ optional($h->item)->item_name ?? '-' }}</div>
                            <div class="font-mono text-indigo-600 font-bold text-xs mt-0.5">{{ optional($h->item)->item_sku ?? '-' }}</div>
                            <div class="text-slate-500 text-xs font-medium mt-1">Warehouse: <strong class="text-slate-800">{{ optional($h->warehouse)->warehouse_name ?? '-' }}</strong></div>
                        </div>

                        <div class="bg-slate-50/70 rounded-xl p-3 border border-slate-100">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Referensi</span>
                            <p class="text-slate-700 text-xs font-mono">{{ $h->history_change_description ?? '-' }}</p>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 font-mono text-xs">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Before</span>
                                <span class="font-bold text-slate-700">{{ $h->history_before }}</span>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Change</span>
                                <span class="font-black {{ ($h->history_change__type === 'increased' || $h->history_change__type === 'in') ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ ($h->history_change__type === 'increased' || $h->history_change__type === 'in') ? '+' : '-' }}{{ $h->history_change }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Now</span>
                                <span class="font-black text-slate-900">{{ $h->history_now }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Tidak ada riwayat pergerakan item ditemukan.
                    </div>
                    @endforelse
                </div>

            </div>

        </div>

    </div>
</div>
@endsection