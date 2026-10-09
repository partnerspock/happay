@extends('layouts.app')

@section('title', 'Stock History Audit - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">Stock History Audit</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Riwayat lengkap perubahan saldo stok dan audit log pergerakan item gudang.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('warehouse.stock-history') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
            
            <!-- Search Box -->
            <div class="md:col-span-2">
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? request('search', '') }}" 
                       placeholder="Cari Nama Item atau SKU..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Filter Warehouse -->
            <div>
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Warehouse</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ ($selectedWarehouse ?? request('warehouse_id')) == $wh->id ? 'selected' : '' }}>
                            [{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Filter & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-4 py-3 md:py-2.5 rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                @if(request('search') || request('warehouse_id'))
                    <a href="{{ route('warehouse.stock-history') }}" class="px-3 py-3 md:py-2.5 bg-slate-100 hover:bg-slate-200 text-rose-500 hover:text-rose-700 text-xs font-bold rounded-xl flex items-center justify-center transition" title="Reset Filter">
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
                        <th class="py-3.5 px-6">Warehouse</th>
                        <th class="py-3.5 px-6">Item & SKU</th>
                        <th class="py-3.5 px-6 text-center">Before</th>
                        <th class="py-3.5 px-6 text-center">Change</th>
                        <th class="py-3.5 px-6 text-center">Now</th>
                        <th class="py-3.5 px-6">Type & Description</th>
                        <th class="py-3.5 px-6 text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($histories as $hist)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6 font-semibold text-slate-900">
                            [{{ $hist->warehouse->region->region_alias ?? 'N/A' }}] {{ $hist->warehouse->warehouse_name ?? '-' }}
                        </td>
                        <td class="py-4 px-6">
                            <div class="font-semibold text-slate-900">{{ $hist->item->item_name ?? '-' }}</div>
                            <div class="text-[11px] font-mono text-slate-400">{{ $hist->item->item_sku ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-6 text-center font-mono text-slate-600">
                            {{ $hist->history_before }}
                        </td>
                        <td class="py-4 px-6 text-center font-mono font-bold {{ $hist->history_change__type === 'increased' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $hist->history_change__type === 'increased' ? '+' : '-' }}{{ $hist->history_change }}
                        </td>
                        <td class="py-4 px-6 text-center font-mono font-bold text-slate-900">
                            {{ $hist->history_now }}
                        </td>
                        <td class="py-4 px-6">
                            @if($hist->history_change__type === 'increased')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px] inline-block mb-1">Increased</span>
                            @else
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px] inline-block mb-1">Decreased</span>
                            @endif
                            <div class="text-slate-500 font-mono text-[11px]">{{ $hist->history_change_description }}</div>
                        </td>
                        <td class="py-4 px-6 text-right text-slate-400 font-mono">
                            {{ $hist->created_at->format('d M Y, H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 text-sm">Belum ada catatan riwayat stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100 text-xs">
            @forelse($histories as $hist)
            <div class="p-3.5 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-slate-400 text-[10px]">
                        {{ $hist->created_at->format('d M Y, H:i') }}
                    </span>
                    @if($hist->history_change__type === 'increased')
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px]">Increased</span>
                    @else
                        <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px]">Decreased</span>
                    @endif
                </div>

                <div>
                    <div class="font-bold text-slate-900 text-sm">{{ $hist->item->item_name ?? '-' }}</div>
                    <div class="font-mono text-indigo-600 font-semibold text-[11px] mt-0.5">{{ $hist->item->item_sku ?? '-' }}</div>
                    <div class="text-slate-500 text-[11px] mt-1 font-medium">Warehouse: [{{ $hist->warehouse->region->region_alias ?? 'N/A' }}] {{ $hist->warehouse->warehouse_name ?? '-' }}</div>
                </div>

                <!-- Perubahan Qty (Before -> Change -> Now) -->
                <div class="grid grid-cols-3 gap-2 text-center bg-slate-100/60 p-2 rounded-xl font-mono text-[11px]">
                    <div>
                        <div class="text-[9px] uppercase font-semibold text-slate-400">Before</div>
                        <div class="font-bold text-slate-700">{{ $hist->history_before }}</div>
                    </div>
                    <div>
                        <div class="text-[9px] uppercase font-semibold text-slate-400">Change</div>
                        <div class="font-bold {{ $hist->history_change__type === 'increased' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $hist->history_change__type === 'increased' ? '+' : '-' }}{{ $hist->history_change }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[9px] uppercase font-semibold text-slate-400">Now</div>
                        <div class="font-bold text-slate-900">{{ $hist->history_now }}</div>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="bg-slate-50 rounded-xl p-2.5 font-mono text-[11px] text-slate-600">
                    <div class="text-[9px] uppercase font-semibold text-slate-400 mb-0.5">Description</div>
                    {{ $hist->history_change_description ?? '-' }}
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">Belum ada catatan riwayat stok.</div>
            @endforelse
        </div>

        @if($histories->hasPages())
            <div class="p-3 md:p-4 border-t border-slate-100 bg-slate-50">
                {{ $histories->links() }}
            </div>
        @endif
    </div>

</div>
</div>
@endsection