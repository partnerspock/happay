@extends('layouts.app')

@section('title', 'Stock Transfer Inter-Warehouse - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory movement standard */
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
<div class="w-full h-full overflow-hidden bg-slate-100 app-content-zoom" 
     x-data="{ 
         showModal: false, 
         showDetailModal: false,
         selectedTransfer: null,
         originWarehouseId: '',
         destinationWarehouseId: '',
         itemsList: [{ item_id: '', qty_sent: 1, current_stock: 0 }],
         stockMap: {},
         
         updateStockMap() {
             if (!this.originWarehouseId) return;
             fetch('/api/warehouse-stocks?warehouse_id=' + this.originWarehouseId)
                 .then(res => res.json())
                 .then(data => {
                     this.stockMap = data;
                     this.itemsList.forEach(item => {
                         if (item.item_id) {
                             item.current_stock = this.stockMap[item.item_id] || 0;
                         }
                     });
                 })
                 .catch(() => { this.stockMap = {}; });
         },
         
         onItemChange(index) {
             let itemId = this.itemsList[index].item_id;
             this.itemsList[index].current_stock = this.stockMap[itemId] || 0;
         }
     }">
    <div class="h-full overflow-y-auto custom-scrollbar" style="-webkit-overflow-scrolling: touch;">

        {{-- ===== PAGE SHELL ===== --}}
        <div class="p-3.5 sm:p-5 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-20 sm:pb-12">

            {{-- 1. TOP HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                Stock Transfer Inter-Warehouse
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Kelola mutasi dan pemindahan stok <span class="text-indigo-600 font-semibold">antar gudang cabang/region</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <button type="button" @click="showModal = true"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 active:scale-95 text-white px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-lg shadow-indigo-600/25 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Buat Transfer Stok</span>
                    </button>
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

            {{-- 2. FILTER & SEARCH COMPONENT (Fixed 12-col layout to avoid offside) --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form action="{{ route('transfer.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        
                        {{-- Search Input (Col 3) --}}
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Cari ID Transfer
                            </label>
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
                                </svg>
                                <input type="text" name="search" value="{{ $search ?? request('search', '') }}" placeholder="Cari ID Transfer..." 
                                       class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                            </div>
                        </div>

                        {{-- Origin Warehouse (Col 3) --}}
                        <div class="lg:col-span-3">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Gudang Asal
                            </label>
                            <select name="warehouse_origin_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="">Semua Gudang Asal</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ ($originWarehouseId ?? request('warehouse_origin_id')) == $wh->id ? 'selected' : '' }}>
                                        [{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Destination Warehouse (Col 3) --}}
                        <div class="lg:col-span-3">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Gudang Tujuan
                            </label>
                            <select name="warehouse_destination_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="">Semua Gudang Tujuan</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ ($destinationWarehouseId ?? request('warehouse_destination_id')) == $wh->id ? 'selected' : '' }}>
                                        [{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status (Col 2) --}}
                        <div class="lg:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Status
                            </label>
                            <select name="transfer_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ ($selectedStatus ?? request('transfer_status')) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="in_transit" {{ ($selectedStatus ?? request('transfer_status')) === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="completed" {{ ($selectedStatus ?? request('transfer_status')) === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ ($selectedStatus ?? request('transfer_status')) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        {{-- Filter & Reset Buttons (Col 1) --}}
                        <div class="sm:col-span-2 lg:col-span-1 flex items-center gap-1.5">
                            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white h-[42px] px-2.5 rounded-xl font-bold text-xs transition flex items-center justify-center shadow-sm" title="Terapkan Filter">
                                <span>Filter</span>
                            </button>
                            <a href="{{ route('transfer.index') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition active:scale-95" title="Reset Filter">
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
                
                {{-- A. DESKTOP TABLE VIEW --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                            <tr>
                                <th class="py-3.5 px-4">Transfer ID &amp; Tanggal</th>
                                <th class="py-3.5 px-4">Gudang Asal</th>
                                <th class="py-3.5 px-4">Gudang Tujuan</th>
                                <th class="py-3.5 px-3 text-center">Total SKU</th>
                                <th class="py-3.5 px-3 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($transfers as $trf)
                            <tr class="hover:bg-indigo-50/25 transition group">
                                <td class="py-3.5 px-4">
                                    <span class="text-slate-900 font-mono font-bold bg-slate-100 group-hover:bg-white px-2.5 py-1 rounded-lg border border-slate-200 text-xs">
                                        {{ $trf->transfer_id }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                        {{ optional($trf->created_at)->format('d M Y, H:i') }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 mr-1">
                                        {{ $trf->originWarehouse->region->region_alias ?? 'N/A' }}
                                    </span>
                                    {{ $trf->originWarehouse->warehouse_name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200/80 mr-1">
                                        {{ $trf->destinationWarehouse->region->region_alias ?? 'N/A' }}
                                    </span>
                                    {{ $trf->destinationWarehouse->warehouse_name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full font-bold text-[10px] border border-slate-200">
                                        {{ $trf->items->count() }} Item
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($trf->transfer_status === 'draft')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold text-[10px] bg-slate-100 text-slate-600 border border-slate-200">Draft</span>
                                    @elseif($trf->transfer_status === 'in_transit')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold text-[10px] bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block animate-pulse"></span>
                                            In Transit
                                        </span>
                                    @elseif($trf->transfer_status === 'completed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            Completed
                                        </span>
                                    @elseif($trf->transfer_status === 'cancelled')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200">Cancelled</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('transfer.print', $trf->id) }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg transition text-[11px]" title="Cetak Dokumen Transfer">
                                            <span>🖨️ Print</span>
                                        </a>

                                        <button @click="selectedTransfer = {{ \Illuminate\Support\Js::from($trf) }}; showDetailModal = true;" 
                                                class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg transition text-[11px]">
                                            Detail
                                        </button>

                                        @if($trf->transfer_status === 'draft')
                                            <form action="{{ route('transfer.update-status', $trf->id) }}" method="POST" class="inline" onsubmit="return confirm('Kirim transfer ini? Stok gudang asal akan dipotong.')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="transfer_status" value="in_transit">
                                                <button type="submit" class="inline-flex items-center gap-1 font-bold text-amber-800 bg-amber-100/80 hover:bg-amber-200 px-2.5 py-1.5 rounded-lg transition text-[11px] shadow-sm">
                                                    Kirim
                                                </button>
                                            </form>
                                        @endif

                                        @if($trf->transfer_status === 'in_transit')
                                            <form action="{{ route('transfer.update-status', $trf->id) }}" method="POST" class="inline" onsubmit="return confirm('Selesaikan transfer ini? Stok akan ditambahkan ke gudang tujuan.')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="transfer_status" value="completed">
                                                <button type="submit" class="inline-flex items-center gap-1 font-bold text-emerald-800 bg-emerald-100/80 hover:bg-emerald-200 px-2.5 py-1.5 rounded-lg transition text-[11px] shadow-sm">
                                                    Terima
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Belum ada dokumen transfer stok.</p>
                                        <p class="text-slate-400 text-xs">Silakan buat transfer baru atau sesuaikan filter pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($transfers as $trf)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-mono font-bold text-xs bg-slate-100 text-slate-900 px-2.5 py-1 rounded-lg border border-slate-200">
                                {{ $trf->transfer_id }}
                            </span>
                            @if($trf->transfer_status === 'draft')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Draft</span>
                            @elseif($trf->transfer_status === 'in_transit')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">In Transit</span>
                            @elseif($trf->transfer_status === 'completed')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>
                            @elseif($trf->transfer_status === 'cancelled')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Cancelled</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Gudang Asal</span>
                                <span class="font-bold text-slate-800 truncate block">[{{ $trf->originWarehouse->region->region_alias ?? 'N/A' }}] {{ $trf->originWarehouse->warehouse_name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Gudang Tujuan</span>
                                <span class="font-bold text-slate-800 truncate block">[{{ $trf->destinationWarehouse->region->region_alias ?? 'N/A' }}] {{ $trf->destinationWarehouse->warehouse_name ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-500 font-medium px-1">
                            <span>Total SKU: <strong class="text-slate-800">{{ $trf->items->count() }} Item</strong></span>
                            <span class="font-mono text-[10px]">{{ optional($trf->created_at)->format('d/m/y H:i') }}</span>
                        </div>

                        <!-- Aksi Tombol HP -->
                        <div class="flex flex-wrap gap-2 pt-1">
                            <a href="{{ route('transfer.print', $trf->id) }}" target="_blank" class="flex-1 text-center bg-slate-100 active:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-bold text-xs">
                                🖨️ Print
                            </a>
                            <button @click="selectedTransfer = {{ \Illuminate\Support\Js::from($trf) }}; showDetailModal = true;" class="flex-1 text-center bg-indigo-50 active:bg-indigo-100 text-indigo-700 py-2.5 rounded-xl font-bold text-xs">
                                Detail
                            </button>

                            @if($trf->transfer_status === 'draft')
                                <form action="{{ route('transfer.update-status', $trf->id) }}" method="POST" class="w-full" onsubmit="return confirm('Kirim transfer ini?')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="transfer_status" value="in_transit">
                                    <button type="submit" class="w-full bg-amber-500 active:bg-amber-600 text-white py-2.5 rounded-xl font-bold text-xs shadow-sm">
                                        Kirim (In Transit)
                                    </button>
                                </form>
                            @endif

                            @if($trf->transfer_status === 'in_transit')
                                <form action="{{ route('transfer.update-status', $trf->id) }}" method="POST" class="w-full" onsubmit="return confirm('Selesaikan transfer ini?')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="transfer_status" value="completed">
                                    <button type="submit" class="w-full bg-emerald-600 active:bg-emerald-700 text-white py-2.5 rounded-xl font-bold text-xs shadow-sm">
                                        Terima &amp; Complete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Belum ada dokumen transfer stok.
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if(method_exists($transfers, 'hasPages') && $transfers->hasPages())
                <div class="p-3.5 sm:p-4 border-t border-slate-100 bg-slate-50/60">
                    {{ $transfers->links() }}
                </div>
                @endif
            </div>

        </div>

        {{-- MODAL 1: CREATE TRANSFER --}}
        <div x-cloak x-show="showModal" x-transition.opacity
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4"
             @keydown.escape.window="showModal = false">
            <div @click.outside="showModal = false"
                 class="w-full sm:max-w-2xl lg:max-w-3xl rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl overflow-hidden max-h-[94dvh] sm:max-h-[90vh] flex flex-col border border-slate-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-indigo-50/80 via-white to-slate-50 p-4 sm:p-5 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-extrabold text-slate-900">Buat Stock Transfer Baru</h2>
                            <p class="text-[10px] sm:text-xs text-slate-400">Pilih gudang asal &amp; tujuan, lalu tambahkan item beserta kuantitas transfer.</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false"
                            class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('transfer.store') }}" method="POST" 
                      class="overflow-y-auto flex-1 p-4 sm:p-6 text-xs space-y-4 custom-scrollbar">
                    @csrf
                    
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-indigo-600 mb-2.5">
                            1. Informasi Gudang Asal &amp; Tujuan
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                    Gudang Asal (Origin) <span class="text-rose-500">*</span>
                                </label>
                                <select name="warehouse_origin_id" x-model="originWarehouseId" @change="updateStockMap(); if(destinationWarehouseId == originWarehouseId) destinationWarehouseId = '';" required 
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                    <option value="" disabled selected>-- Pilih Gudang Asal --</option>
                                    @foreach($userOriginWarehouses as $wh)
                                        <option value="{{ $wh->id }}">[{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                    Gudang Tujuan (Destination) <span class="text-rose-500">*</span>
                                </label>
                                <select name="warehouse_destination_id" x-model="destinationWarehouseId" required 
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                    <option value="" disabled selected>-- Pilih Gudang Tujuan --</option>
                                    @foreach($warehouses as $wh)
                                        <template x-if="originWarehouseId != {{ $wh->id }}">
                                            <option value="{{ $wh->id }}">[{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}</option>
                                        </template>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border border-slate-200/90 rounded-2xl overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between bg-slate-50/90 px-4 py-3 border-b border-slate-200">
                            <div>
                                <span class="font-extrabold text-slate-800 text-xs uppercase tracking-wide">Daftar Barang &amp; Qty Transfer</span>
                                <span class="text-rose-500 font-bold">*</span>
                                <p class="text-[10px] text-amber-600 font-medium mt-0.5" x-show="!originWarehouseId">⚠️ Pilih Gudang Asal terlebih dahulu untuk memuat daftar stok.</p>
                            </div>
                            <button type="button" @click="itemsList.push({ item_id: '', qty_sent: 1, current_stock: 0 })" 
                                    class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg font-bold text-xs transition active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                <span>+ Tambah Barang</span>
                            </button>
                        </div>

                        <div class="p-3 sm:p-4 bg-white space-y-2.5">
                            <template x-for="(row, index) in itemsList" :key="index">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                                    <div class="flex-1 min-w-0">
                                        <select :name="'items[' + index + '][item_id]'" 
                                                x-model="row.item_id" 
                                                @change="onItemChange(index)" 
                                                required 
                                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                            <option value="" disabled>-- Pilih Item Barang --</option>
                                            @foreach($items as $item)
                                                <template x-if="!originWarehouseId || (stockMap['{{ $item->id }}'] && stockMap['{{ $item->id }}'] > 0)">
                                                    <option value="{{ $item->id }}" 
                                                            :disabled="itemsList.some((it, i) => i !== index && it.item_id == '{{ $item->id }}')"
                                                            :class="itemsList.some((it, i) => i !== index && it.item_id == '{{ $item->id }}') ? 'bg-slate-100 text-slate-400 font-normal' : ''">
                                                        [{{ $item->item_sku }}] {{ $item->item_name }} {{ $item->item_variant ? "({$item->item_variant})" : '' }} 
                                                        <span x-text="'(Stok: ' + (stockMap['{{ $item->id }}'] || 0) + ')'"></span>
                                                    </option>
                                                </template>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0 justify-end">
                                        <div class="text-center px-2.5 py-1.5 bg-white border border-slate-200 rounded-xl">
                                            <span class="text-[8px] text-slate-400 block font-bold uppercase">Stok Gudang</span>
                                            <span class="font-bold font-mono text-xs" :class="row.current_stock > 0 ? 'text-emerald-600' : 'text-rose-500'" x-text="row.current_stock"></span>
                                        </div>

                                        <div class="w-24">
                                            <input type="number" 
                                                   :name="'items[' + index + '][qty_sent]'" 
                                                   x-model="row.qty_sent" 
                                                   min="1" 
                                                   required 
                                                   placeholder="Qty" 
                                                   class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-white text-xs text-center font-mono font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                        </div>

                                        <button type="button" @click="itemsList.splice(index, 1)" x-show="itemsList.length > 1"
                                                class="w-8 h-8 flex items-center justify-center text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                            Catatan Transfer (Opsional)
                        </label>
                        <textarea name="transfer_notes" rows="2" placeholder="Catatan tambahan alokasi atau instruksi transfer..." 
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition resize-none"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showModal = false"
                                class="flex-1 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition text-xs">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/25 transition text-xs active:scale-95">
                            Simpan Dokumen
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: DETAIL TRANSFER --}}
        <div x-cloak x-show="showDetailModal" x-transition.opacity
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4"
             @keydown.escape.window="showDetailModal = false">
            <div @click.outside="showDetailModal = false"
                 class="w-full sm:max-w-lg lg:max-w-xl rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl overflow-hidden max-h-[92dvh] sm:max-h-[88vh] flex flex-col border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4 sm:p-5 shrink-0">
                    <div>
                        <span class="text-base font-extrabold text-slate-900 font-mono" x-text="selectedTransfer ? selectedTransfer.transfer_id : ''"></span>
                        <p class="text-xs text-slate-400 mt-0.5">Detail Dokumen Stock Transfer Inter-Warehouse</p>
                    </div>
                    <button type="button" @click="showDetailModal = false" 
                            class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4 p-4 sm:p-6 overflow-y-auto flex-1 text-xs custom-scrollbar" x-show="selectedTransfer">
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                        <div>
                            <span class="text-slate-400 font-bold uppercase text-[9px] block mb-0.5">Gudang Asal</span>
                            <span class="font-bold text-slate-800" x-text="selectedTransfer?.origin_warehouse?.warehouse_name || '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-bold uppercase text-[9px] block mb-0.5">Gudang Tujuan</span>
                            <span class="font-bold text-slate-800" x-text="selectedTransfer?.destination_warehouse?.warehouse_name || '-'"></span>
                        </div>
                    </div>

                    <div>
                        <span class="font-bold text-slate-500 uppercase text-[9px] tracking-widest block mb-2">Item Transfer</span>
                        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-[9px] text-slate-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="p-3">SKU / Item</th>
                                        <th class="p-3 text-center">Qty Sent</th>
                                        <th class="p-3 text-center">Qty Received</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <template x-for="item in selectedTransfer?.items" :key="item.id">
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="p-3">
                                                <div class="font-bold text-slate-800" x-text="item.item_name || '-'"></div>
                                                <div class="text-[10px] font-mono text-slate-400 mt-0.5" x-text="item.item_sku + (item.item_sku_variant ? ' / ' + item.item_sku_variant : '')"></div>
                                            </td>
                                            <td class="p-3 text-center font-mono font-bold text-amber-600" x-text="item.qty_sent"></td>
                                            <td class="p-3 text-center font-mono font-bold text-emerald-600" x-text="item.qty_received"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <template x-if="selectedTransfer?.transfer_notes">
                        <div class="bg-slate-50 rounded-2xl border border-slate-100 p-3.5">
                            <span class="font-bold text-slate-500 uppercase text-[9px] tracking-widest block mb-1">Catatan Dokumen</span>
                            <p class="text-slate-700 font-medium" x-text="selectedTransfer.transfer_notes"></p>
                        </div>
                    </template>
                </div>

                <div class="p-4 border-t border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/50">
                    <template x-if="selectedTransfer">
                        <a :href="'/transfer/' + selectedTransfer.id + '/print'" target="_blank"
                           class="px-4 py-2 rounded-xl font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition text-xs inline-flex items-center gap-1.5">
                            <span>🖨️ Cetak Dokumen</span>
                        </a>
                    </template>
                    <button type="button" @click="showDetailModal = false"
                            class="px-5 py-2.5 rounded-xl font-bold text-slate-600 bg-slate-200 hover:bg-slate-300 transition text-xs ml-auto">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection