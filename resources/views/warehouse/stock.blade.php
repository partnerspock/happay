@extends('layouts.app')

@section('title', 'List Stocks - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">List Stocks</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Pemantauan saldo stok berjalan per gudang.</p>
        </div>
        <a href="{{ route('warehouse.stock-history') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs transition border border-slate-200 flex items-center justify-center shrink-0">
            📜 Lihat Stock History
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('warehouse.stock') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ $search ?? request('search', '') }}" placeholder="Cari nama barang atau SKU..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Warehouse</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ ($warehouseId ?? request('warehouse_id')) == $wh->id ? 'selected' : '' }}>
                            [{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-4 py-3 md:py-2.5 rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                @if(($search ?? request('search')) || ($warehouseId ?? request('warehouse_id')))
                    <a href="{{ route('warehouse.stock') }}" class="px-3 py-3 md:py-2.5 bg-slate-100 hover:bg-slate-200 text-rose-500 hover:text-rose-700 text-xs font-bold rounded-xl flex items-center justify-center transition" title="Reset Filter">
                        🔄
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        {{-- ============ TABEL (iPad & desktop, >= 768px) ============ --}}
        <div class="hidden md:block overflow-x-auto max-h-[65vh]">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 bg-slate-50/80 border-b border-slate-200/80 text-xs text-slate-500 uppercase tracking-wider font-semibold z-10">
                    <tr>
                        <th class="py-3.5 px-6 text-center">Date</th>
                        <th class="py-3.5 px-6">SKU & Item</th>
                        <th class="py-3.5 px-6">Warehouse</th>
                        <th class="py-3.5 px-6 text-center">Total Stok</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($stocks as $stock)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6 text-center font-mono text-slate-400">
                            {{ $stock->updated_at ? $stock->updated_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-900">{{ $stock->item_variant ?? $stock->item->item_variant ?? '-' }}</div>
                            <div class="font-mono text-slate-400 mt-0.5">
                                {{ $stock->item->item_sku ?? '-' }} 
                                @if($stock->item->item_variant ?? null)
                                    <span class="text-slate-500">({{ $stock->item->item_variant }})</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-800">
                            [{{ $stock->warehouse->region->region_alias ?? 'N/A' }}] {{ $stock->warehouse->warehouse_name ?? '-' }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="font-mono font-bold px-3 py-1 rounded-full {{ $stock->stock_now > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ number_format($stock->stock_now) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="{{ route('warehouse.stock.item-history', ['warehouse_id' => $stock->warehouse_id, 'item_id' => $stock->item_id]) }}" target="_blank" 
                            class="font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition inline-flex items-center space-x-1">
                                <span>History&nbsp🔍</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-sm">Belum ada data stok barang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100 text-xs">
            @forelse($stocks as $stock)
            <div class="p-3.5 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-slate-400 text-[10px]">
                        {{ $stock->updated_at ? $stock->updated_at->format('d/m/Y H:i') : '-' }}
                    </span>
                    <span class="font-mono font-bold px-2.5 py-0.5 rounded-full text-[10px] {{ $stock->stock_now > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        Stok: {{ number_format($stock->stock_now) }}
                    </span>
                </div>

                <div>
                    <div class="font-bold text-slate-900 text-sm">{{ $stock->item_variant ?? $stock->item->item_variant ?? '-' }}</div>
                    <div class="font-mono text-indigo-600 font-semibold mt-0.5 text-[11px]">{{ $stock->item->item_sku ?? '-' }}</div>
                    <div class="text-slate-500 mt-1 font-medium text-[11px]">Warehouse: [{{ $stock->warehouse->region->region_alias ?? 'N/A' }}] {{ $stock->warehouse->warehouse_name ?? '-' }}</div>
                </div>

                <div class="pt-1">
                    <a href="{{ route('warehouse.stock.item-history', ['warehouse_id' => $stock->warehouse_id, 'item_id' => $stock->item_id]) }}" target="_blank" 
                    class="w-full bg-indigo-50 text-indigo-600 active:bg-indigo-100 py-2.5 rounded-xl font-semibold transition flex items-center justify-center space-x-1">
                        <span>Lihat History 🔍</span>
                    </a>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">Belum ada data stok barang.</div>
            @endforelse
        </div>

        @if($stocks->hasPages())
            <div class="p-3 md:p-4 border-t border-slate-100 bg-slate-50">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>

</div>
</div>
@endsection