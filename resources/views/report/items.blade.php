@extends('layouts.app')

@section('title', 'Item Report - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">Item Movement Report</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Laporan pergerakan stok masuk dan keluar (in/out) beserta referensi transaksi atau transfer.</p>
        </div>
        <div class="flex items-center space-x-3 shrink-0">
            <a href="{{ route('report.items.export.csv') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center space-x-2">
                <span>📥 Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar: 1 kolom (HP) -> 2 kolom (iPad portrait) -> 5 kolom (desktop) -->
    <form action="{{ route('report.items') }}" method="GET" class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 items-end">
        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Warehouse</label>
            <select name="warehouse_id" 
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none {{ !empty($lockedWarehouseId) ? 'opacity-60 cursor-not-allowed' : '' }}"
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

        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none">
        </div>

        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Tanggal Selesai</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none">
        </div>

        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Cari Item / SKU</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Nama barang / SKU..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition">
                Filter Data
            </button>
            <a href="{{ route('report.items') }}" class="px-4 md:px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center font-bold text-xs transition" title="Reset Filter">
                🔄
            </a>
        </div>
    </form>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-3 md:p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <span class="text-xs font-bold text-slate-600">Riwayat Pergerakan Item</span>
            <span class="text-xs font-mono font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg">
                Total: {{ $histories->count() }} Log
            </span>
        </div>
        
        {{-- ============ TABEL (iPad & desktop, >= 768px) ============ --}}
        <div class="hidden md:block overflow-x-auto max-h-[65vh]">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 bg-slate-50 border-b border-slate-200/80 text-xs text-slate-500 uppercase tracking-wider font-semibold z-10">
                    <tr>
                        <th class="py-3 px-6">Waktu</th>
                        <th class="py-3 px-6">Warehouse</th>
                        <th class="py-3 px-6">Nama Item & SKU</th>
                        <th class="py-3 px-6">Referensi</th>
                        <th class="py-3 px-6 text-center">Tipe</th>
                        <th class="py-3 px-6 text-center">Qty Before</th>
                        <th class="py-3 px-6 text-center">Change</th>
                        <th class="py-3 px-6 text-center">Qty Now</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($histories as $h)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-6 font-mono text-slate-500">{{ $h->created_at ? $h->created_at->format('d M Y, H:i') : '-' }}</td>
                        <td class="py-3.5 px-6 font-semibold">{{ optional($h->warehouse)->warehouse_name ?? '-' }}</td>
                        <td class="py-3.5 px-6">
                            <div class="font-bold text-slate-900">{{ optional($h->item)->item_name ?? '-' }}</div>
                            <div class="text-[10px] text-indigo-600 font-mono font-semibold">{{ optional($h->item)->item_sku ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-6 font-mono text-slate-600 text-[11px]">{{ $h->history_change_description ?? '-' }}</td>
                        <td class="py-3.5 px-6 text-center">
                            @if($h->history_change__type === 'increased' || $h->history_change__type === 'in')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-bold">IN (+)</span>
                            @else
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-bold">OUT (-)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-6 text-center font-mono">{{ $h->history_before }}</td>
                        <td class="py-3.5 px-6 text-center font-mono font-bold {{ $h->history_change__type === 'increased' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $h->history_change__type === 'increased' ? '+' : '-' }}{{ $h->history_change }}
                        </td>
                        <td class="py-3.5 px-6 text-center font-mono font-bold text-slate-900">{{ $h->history_now }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-16 text-center text-slate-400 text-sm">Tidak ada riwayat pergerakan item ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($histories as $h)
            <div class="p-3.5 space-y-2.5 text-xs">
                <!-- Baris 1: Waktu & Status Tipe -->
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono text-slate-400 text-[10px]">{{ $h->created_at ? $h->created_at->format('d M Y, H:i') : '-' }}</span>
                    @if($h->history_change__type === 'increased' || $h->history_change__type === 'in')
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-bold text-[10px]">IN (+)</span>
                    @else
                        <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-bold text-[10px]">OUT (-)</span>
                    @endif
                </div>

                <!-- Baris 2: Info Item & Warehouse -->
                <div>
                    <div class="font-bold text-slate-900">{{ optional($h->item)->item_name ?? '-' }}</div>
                    <div class="text-[10px] text-indigo-600 font-mono font-semibold">{{ optional($h->item)->item_sku ?? '-' }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5 font-medium">Warehouse: {{ optional($h->warehouse)->warehouse_name ?? '-' }}</div>
                </div>

                <!-- Baris 3: Referensi -->
                <div class="bg-slate-50 rounded-xl p-2.5 text-[11px] font-mono text-slate-600">
                    <div class="text-[9px] uppercase font-semibold text-slate-400 mb-0.5">Referensi</div>
                    {{ $h->history_change_description ?? '-' }}
                </div>

                <!-- Baris 4: Perubahan Qty (Before -> Change -> Now) -->
                <div class="grid grid-cols-3 gap-2 text-center bg-slate-100/60 p-2 rounded-xl font-mono text-[11px]">
                    <div>
                        <div class="text-[9px] uppercase font-semibold text-slate-400">Before</div>
                        <div class="font-bold text-slate-700">{{ $h->history_before }}</div>
                    </div>
                    <div>
                        <div class="text-[9px] uppercase font-semibold text-slate-400">Change</div>
                        <div class="font-bold {{ $h->history_change__type === 'increased' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $h->history_change__type === 'increased' ? '+' : '-' }}{{ $h->history_change }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[9px] uppercase font-semibold text-slate-400">Now</div>
                        <div class="font-bold text-slate-900">{{ $h->history_now }}</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="py-14 text-center text-slate-400 text-sm">Tidak ada riwayat pergerakan item ditemukan.</div>
            @endforelse
        </div>

    </div>

    <!-- Spacer bawah supaya tidak mepet -->
    <div class="h-6 md:h-0"></div>

</div>
</div>
@endsection