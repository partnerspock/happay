@extends('layouts.app')

@section('title', 'List Stocks - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory, transfer, warehouse, history & audit standard */
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                List Stocks
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Pemantauan saldo <span class="text-indigo-600 font-semibold">stok berjalan per gudang</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <a href="{{ route('warehouse.stock-history') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm border border-slate-200 transition-all shrink-0">
                        <span>📜</span>
                        <span>Lihat Stock History</span>
                    </a>
                </div>
            </div>

            {{-- 2. FILTER & SEARCH COMPONENT (Fixed 12-col layout) --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form action="{{ route('warehouse.stock') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        
                        {{-- Search Box (Col 5) --}}
                        <div class="sm:col-span-2 lg:col-span-5">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Cari Nama Barang atau SKU
                            </label>
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
                                </svg>
                                <input type="text" name="search" value="{{ $search ?? request('search', '') }}" placeholder="Cari nama barang atau SKU..." 
                                       class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                            </div>
                        </div>

                        {{-- Warehouse Filter (Col 4) --}}
                        <div class="lg:col-span-4">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Filter Gudang
                            </label>
                            <select name="warehouse_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="">Semua Warehouse</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ ($warehouseId ?? request('warehouse_id')) == $wh->id ? 'selected' : '' }}>
                                        [{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter & Reset Buttons (Col 3) --}}
                        <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-1.5">
                            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white h-[42px] px-3 rounded-xl font-bold text-xs transition flex items-center justify-center shadow-sm">
                                <span>Filter</span>
                            </button>
                            @if(($search ?? request('search')) || ($warehouseId ?? request('warehouse_id')))
                                <a href="{{ route('warehouse.stock') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition active:scale-95" title="Reset Filter">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                    </svg>
                                </a>
                            @endif
                        </div>

                    </div>
                </form>
            </div>

            {{-- 3. MAIN DATA VIEW --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                
                {{-- A. DESKTOP TABLE VIEW --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                            <tr>
                                <th class="py-3.5 px-4 text-center">Date Update</th>
                                <th class="py-3.5 px-4">SKU &amp; Item</th>
                                <th class="py-3.5 px-4">Warehouse</th>
                                <th class="py-3.5 px-4 text-center">Total Stok</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($stocks as $stock)
                            <tr class="hover:bg-indigo-50/25 transition">
                                <td class="py-3.5 px-4 text-center font-mono text-slate-400 text-[11px]">
                                    {{ $stock->updated_at ? $stock->updated_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $stock->item_variant ?? $stock->item->item_variant ?? $stock->item->item_name ?? '-' }}</div>
                                    <div class="font-mono text-indigo-600 font-semibold text-[10px] mt-0.5">
                                        {{ $stock->item->item_sku ?? '-' }} 
                                        @if($stock->item->item_variant ?? null)
                                            <span class="text-slate-400">({{ $stock->item->item_variant }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 mr-1">
                                        {{ $stock->warehouse->region->region_alias ?? 'N/A' }}
                                    </span>
                                    {{ $stock->warehouse->warehouse_name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold font-mono text-xs {{ $stock->stock_now > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                        {{ number_format($stock->stock_now) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('warehouse.stock.item-history', ['warehouse_id' => $stock->warehouse_id, 'item_id' => $stock->item_id]) }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition text-[11px]">
                                        <span>History 🔍</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Belum ada data stok barang.</p>
                                        <p class="text-slate-400 text-xs">Sesuaikan filter pencarian atau lakukan mutasi stok untuk melihat data.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($stocks as $stock)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-slate-400 text-[10px]">
                                {{ $stock->updated_at ? $stock->updated_at->format('d/m/Y H:i') : '-' }}
                            </span>
                            <span class="font-mono font-bold px-2.5 py-0.5 rounded-full text-[10px] {{ $stock->stock_now > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                Stok: {{ number_format($stock->stock_now) }}
                            </span>
                        </div>

                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ $stock->item_variant ?? $stock->item->item_variant ?? $stock->item->item_name ?? '-' }}</div>
                            <div class="font-mono text-indigo-600 font-bold text-xs mt-0.5">{{ $stock->item->item_sku ?? '-' }}</div>
                            <div class="text-slate-500 text-xs font-medium mt-1">Warehouse: <strong class="text-slate-800">[{{ $stock->warehouse->region->region_alias ?? 'N/A' }}] {{ $stock->warehouse->warehouse_name ?? '-' }}</strong></div>
                        </div>

                        <div class="pt-1">
                            <a href="{{ route('warehouse.stock.item-history', ['warehouse_id' => $stock->warehouse_id, 'item_id' => $stock->item_id]) }}" target="_blank" rel="noopener noreferrer"
                               class="w-full bg-slate-100 active:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                                <span>Lihat History 🔍</span>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Belum ada data stok barang.
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if(method_exists($stocks, 'hasPages') && $stocks->hasPages())
                <div class="p-3.5 sm:p-4 border-t border-slate-100 bg-slate-50/60">
                    {{ $stocks->links() }}
                </div>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection