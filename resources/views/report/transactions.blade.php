@extends('layouts.app')

@section('title', 'Transaction Report - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">Transaction Report</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Laporan lengkap seluruh transaksi POS berdasarkan rentang tanggal dan cabang.</p>
        </div>
        <div class="flex items-center space-x-3 shrink-0">
            <a href="{{ route('report.transactions.export.csv') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center space-x-2">
                <span>📥 Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <form action="{{ route('report.transactions') }}" method="GET" class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 items-end">
        
        <!-- Filter Warehouse (Locked jika admin punya warehouse khusus) -->
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

        <!-- Date Start -->
        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none">
        </div>

        <!-- Date End -->
        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Tanggal Selesai</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none">
        </div>

        <!-- Search Box -->
        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Cari Transaksi</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="No Invoice / Nama Pelanggan..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none">
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition">
                Filter Data
            </button>
            <a href="{{ route('report.transactions') }}" class="px-4 md:px-3 py-3 md:py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center font-bold text-xs transition" title="Reset Filter">
                🔄
            </a>
        </div>
    </form>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-3 md:p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <span class="text-xs font-bold text-slate-600">Menampilkan Seluruh Data Transaksi</span>
            <span class="text-xs font-mono font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg">
                Total: {{ $transactions->count() }} Transaksi
            </span>
        </div>
        
        {{-- ============ TABEL (iPad & desktop, >= 768px) ============ --}}
        <div class="hidden md:block overflow-x-auto max-h-[65vh]">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 bg-slate-50/80 border-b border-slate-200/80 text-xs text-slate-500 uppercase tracking-wider font-semibold z-10">
                    <tr>
                        <th class="py-3.5 px-6">Invoice UUID</th>
                        <th class="py-3.5 px-6">Waktu</th>
                        <th class="py-3.5 px-6">Pelanggan</th>
                        <th class="py-3.5 px-6">Warehouse</th>
                        <th class="py-3.5 px-6 text-center">Total Item</th>
                        <th class="py-3.5 px-6 text-right">Nominal (Rp)</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6 font-mono font-bold text-indigo-600">{{ $trx->uuid }}</td>
                        <td class="py-4 px-6 text-slate-500 font-mono">{{ $trx->created_at ? $trx->created_at->format('d M Y, H:i') : '-' }}</td>
                        <td class="py-4 px-6 font-semibold text-slate-900">
                            {{ $trx->transaction_info_name ?? 'Guest' }}
                            <span class="block text-[10px] text-slate-400 font-normal">{{ $trx->transaction_info_number }}</span>
                        </td>
                        <td class="py-4 px-6">{{ optional($trx->warehouse)->warehouse_name ?? '-' }}</td>
                        <td class="py-4 px-6 text-center font-bold">{{ $trx->items->sum('transaction_item_qty') }} pcs</td>
                        <td class="py-4 px-6 text-right font-mono font-bold text-slate-900">
                            Rp {{ number_format($trx->transaction_pricetotal, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            @if($trx->transaction_status === 'completed')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold">Completed</span>
                            @elseif($trx->transaction_status === 'draft')
                                <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full font-semibold">Draft</span>
                            @else
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold">{{ ucfirst($trx->transaction_status) }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <a href="{{ route('pos.print', $trx->id) }}" target="_blank" class="font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition inline-block">
                                Print Struk
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400 text-sm">Tidak ada data transaksi ditemukan dalam rentang tanggal tersebut.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100 text-xs">
            @forelse($transactions as $trx)
            <div class="p-3.5 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono font-bold text-indigo-600 truncate">{{ $trx->uuid }}</span>
                    @if($trx->transaction_status === 'completed')
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px] shrink-0">Completed</span>
                    @elseif($trx->transaction_status === 'draft')
                        <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px] shrink-0">Draft</span>
                    @else
                        <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px] shrink-0">{{ ucfirst($trx->transaction_status) }}</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl font-medium">
                    <div>
                        <span class="text-[9px] uppercase font-semibold text-slate-400 block">Pelanggan</span>
                        <span class="font-semibold text-slate-900 truncate block">{{ $trx->transaction_info_name ?? 'Guest' }}</span>
                        <span class="text-[10px] text-slate-400 font-mono block">{{ $trx->transaction_info_number ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase font-semibold text-slate-400 block">Warehouse</span>
                        <span class="text-slate-800 truncate block">{{ optional($trx->warehouse)->warehouse_name ?? '-' }}</span>
                        <span class="text-[10px] text-slate-400 font-mono block">{{ $trx->created_at ? $trx->created_at->format('d M Y, H:i') : '-' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between bg-slate-100/60 p-2 rounded-xl font-mono">
                    <div>
                        <span class="text-[9px] uppercase font-semibold text-slate-400 block">Total Item</span>
                        <span class="font-bold text-slate-700">{{ $trx->items->sum('transaction_item_qty') }} pcs</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] uppercase font-semibold text-slate-400 block">Nominal</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($trx->transaction_pricetotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="pt-1">
                    <a href="{{ route('pos.print', $trx->id) }}" target="_blank" class="w-full bg-indigo-50 text-indigo-600 active:bg-indigo-100 py-2.5 rounded-xl font-semibold transition text-center block">
                        🖨️ Print Struk
                    </a>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">Tidak ada data transaksi ditemukan dalam rentang tanggal tersebut.</div>
            @endforelse
        </div>

    </div>

    <!-- Spacer bawah supaya tidak mepet -->
    <div class="h-6 md:h-0"></div>

</div>
</div>
@endsection