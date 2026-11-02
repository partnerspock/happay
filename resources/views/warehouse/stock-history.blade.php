@extends('layouts.app')

@section('title', 'Stock History Audit - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory, transfer, warehouse & detail standard */
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                Stock History Audit
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Riwayat lengkap perubahan saldo stok dan <span class="text-indigo-600 font-semibold">audit log pergerakan item gudang</span>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. FILTER & SEARCH COMPONENT (Fixed 12-col layout) --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form action="{{ route('warehouse.stock-history') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        
                        {{-- Search Box (Col 5) --}}
                        <div class="sm:col-span-2 lg:col-span-5">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Cari Nama Item atau SKU
                            </label>
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
                                </svg>
                                <input type="text" name="search" value="{{ $search ?? request('search', '') }}" placeholder="Cari Nama Item atau SKU..." 
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
                                    <option value="{{ $wh->id }}" {{ ($selectedWarehouse ?? request('warehouse_id')) == $wh->id ? 'selected' : '' }}>
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
                            @if(request('search') || request('warehouse_id'))
                                <a href="{{ route('warehouse.stock-history') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition active:scale-95" title="Reset Filter">
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
                                <th class="py-3.5 px-4">Warehouse</th>
                                <th class="py-3.5 px-4">Item &amp; SKU</th>
                                <th class="py-3.5 px-4 text-center">Before</th>
                                <th class="py-3.5 px-4 text-center">Change</th>
                                <th class="py-3.5 px-4 text-center">Now</th>
                                <th class="py-3.5 px-4">Type &amp; Description</th>
                                <th class="py-3.5 px-4 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($histories as $hist)
                            <tr class="hover:bg-indigo-50/25 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 mr-1">
                                        {{ $hist->warehouse->region->region_alias ?? 'N/A' }}
                                    </span>
                                    {{ $hist->warehouse->warehouse_name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $hist->item->item_name ?? '-' }}</div>
                                    <div class="text-[10px] font-mono text-indigo-600 font-semibold mt-0.5">{{ $hist->item->item_sku ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-600">
                                    {{ $hist->history_before }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold {{ $hist->history_change__type === 'increased' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $hist->history_change__type === 'increased' ? '+' : '-' }}{{ $hist->history_change }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-extrabold text-slate-900">
                                    {{ $hist->history_now }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($hist->history_change__type === 'increased')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200 mb-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                            Increased
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200 mb-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                            Decreased
                                        </span>
                                    @endif
                                    <div class="text-slate-600 font-medium text-[11px]">{{ $hist->history_change_description ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-slate-400 text-[11px]">
                                    {{ optional($hist->created_at)->format('d M Y, H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Belum ada catatan riwayat stok.</p>
                                        <p class="text-slate-400 text-xs">Sesuaikan filter pencarian atau lakukan mutasi stok untuk melihat audit log.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($histories as $hist)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-slate-400 text-[10px]">
                                {{ optional($hist->created_at)->format('d M Y, H:i') }}
                            </span>
                            @if($hist->history_change__type === 'increased')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Increased</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Decreased</span>
                            @endif
                        </div>

                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ $hist->item->item_name ?? '-' }}</div>
                            <div class="font-mono text-indigo-600 font-bold text-xs mt-0.5">{{ $hist->item->item_sku ?? '-' }}</div>
                            <div class="text-slate-500 text-xs font-medium mt-1">Warehouse: <strong class="text-slate-800">[{{ $hist->warehouse->region->region_alias ?? 'N/A' }}] {{ $hist->warehouse->warehouse_name ?? '-' }}</strong></div>
                        </div>

                        {{-- Perubahan Qty Card --}}
                        <div class="grid grid-cols-3 gap-2 text-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 font-mono text-xs">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Before</span>
                                <span class="font-bold text-slate-700">{{ $hist->history_before }}</span>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Change</span>
                                <span class="font-black {{ $hist->history_change__type === 'increased' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $hist->history_change__type === 'increased' ? '+' : '-' }}{{ $hist->history_change }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Now</span>
                                <span class="font-black text-slate-900">{{ $hist->history_now }}</span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="bg-slate-50/70 rounded-xl p-3 border border-slate-100">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Description</span>
                            <p class="text-slate-700 text-xs font-medium">{{ $hist->history_change_description ?? '-' }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Belum ada catatan riwayat stok.
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if(method_exists($histories, 'hasPages') && $histories->hasPages())
                <div class="p-3.5 sm:p-4 border-t border-slate-100 bg-slate-50/60">
                    {{ $histories->links() }}
                </div>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection