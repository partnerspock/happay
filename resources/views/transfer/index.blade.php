@extends('layouts.app')

@section('title', 'Stock Transfer Inter-Warehouse - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;" 
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
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">Stock Transfer Inter-Warehouse</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola mutasi dan pemindahan stok antar gudang cabang/region.</p>
        </div>
        <button @click="showModal = true" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center space-x-2 shrink-0">
            <span>+ Buat Transfer Stok</span>
        </button>
    </div>

    <!-- Filter Bar Component -->
    <div class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('transfer.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-center">
            
            <!-- Search Box -->
            <div class="min-w-0">
                <input type="text" name="search" value="{{ $search ?? request('search', '') }}" placeholder="Cari ID Transfer..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Filter Origin Warehouse -->
            <div class="min-w-0">
                <select name="warehouse_origin_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Gudang Asal</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ ($originWarehouseId ?? request('warehouse_origin_id')) == $wh->id ? 'selected' : '' }}>
                            [{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Destination Warehouse -->
            <div class="min-w-0">
                <select name="warehouse_destination_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Gudang Tujuan</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ ($destinationWarehouseId ?? request('warehouse_destination_id')) == $wh->id ? 'selected' : '' }}>
                            [{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div class="min-w-0">
                <select name="transfer_status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ ($selectedStatus ?? request('transfer_status')) === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="in_transit" {{ ($selectedStatus ?? request('transfer_status')) === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                    <option value="completed" {{ ($selectedStatus ?? request('transfer_status')) === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ ($selectedStatus ?? request('transfer_status')) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Action Filter & Reset -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white py-3 md:py-2.5 rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                @if(request('search') || request('warehouse_origin_id') || request('warehouse_destination_id') || request('transfer_status'))
                    <a href="{{ route('transfer.index') }}" class="px-4 md:px-3 py-3 md:py-2.5 bg-slate-100 hover:bg-slate-200 text-rose-500 hover:text-rose-700 rounded-xl flex items-center justify-center font-bold text-xs transition" title="Reset Filter">
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
                        <th class="py-3.5 px-6">Transfer ID</th>
                        <th class="py-3.5 px-6">Gudang Asal</th>
                        <th class="py-3.5 px-6">Gudang Tujuan</th>
                        <th class="py-3.5 px-6">Total SKU</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($transfers as $trf)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6">
                            <span class="text-indigo-600 font-mono font-bold bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                                {{ $trf->transfer_id }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-900">
                            [{{ $trf->originWarehouse->region->region_alias ?? 'N/A' }}] {{ $trf->originWarehouse->warehouse_name ?? '-' }}
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-900">
                            [{{ $trf->destinationWarehouse->region->region_alias ?? 'N/A' }}] {{ $trf->destinationWarehouse->warehouse_name ?? '-' }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full font-bold">{{ $trf->items->count() }} Item</span>
                        </td>
                        <td class="py-4 px-6">
                            @if($trf->transfer_status === 'draft')
                                <span class="bg-slate-100 text-slate-700 border border-slate-200 px-2.5 py-0.5 rounded-full font-semibold">Draft</span>
                            @elseif($trf->transfer_status === 'in_transit')
                                <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full font-semibold">In Transit</span>
                            @elseif($trf->transfer_status === 'completed')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold">Completed</span>
                            @elseif($trf->transfer_status === 'cancelled')
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold">Cancelled</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                            <!-- Print Button -->
                            <a href="{{ route('transfer.print', $trf->id) }}" target="_blank" 
                               class="font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition inline-flex items-center space-x-1">
                                <span>🖨️ Print</span>
                            </a>

                            <!-- Detail Button -->
                            <button @click="selectedTransfer = {{ json_encode($trf) }}; showDetailModal = true;" 
                                    class="font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg transition">
                                Detail
                            </button>

                            @if($trf->transfer_status === 'draft')
                                <form action="{{ route('transfer.update-status', $trf->id) }}" method="POST" class="inline" onsubmit="return confirm('Kirim transfer ini? Stok gudang asal akan dipotong.')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="transfer_status" value="in_transit">
                                    <button type="submit" class="font-bold text-amber-600 hover:text-amber-800 bg-amber-50 px-3 py-1.5 rounded-lg transition">
                                        Kirim
                                    </button>
                                </form>
                            @endif

                            @if($trf->transfer_status === 'in_transit')
                                <form action="{{ route('transfer.update-status', $trf->id) }}" method="POST" class="inline" onsubmit="return confirm('Selesaikan transfer ini? Stok akan ditambahkan ke gudang tujuan.')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="transfer_status" value="completed">
                                    <button type="submit" class="font-bold text-emerald-600 hover:text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg transition">
                                        Terima
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 text-sm">Belum ada dokumen transfer stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100 text-xs">
            @forelse($transfers as $trf)
            <div class="p-3.5 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-indigo-600 font-mono font-bold bg-indigo-50 px-2 py-0.5 rounded text-[11px]">
                        {{ $trf->transfer_id }}
                    </span>
                    @if($trf->transfer_status === 'draft')
                        <span class="bg-slate-100 text-slate-700 border border-slate-200 px-2 py-0.5 rounded-full font-semibold text-[10px]">Draft</span>
                    @elseif($trf->transfer_status === 'in_transit')
                        <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full font-semibold text-[10px]">In Transit</span>
                    @elseif($trf->transfer_status === 'completed')
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px]">Completed</span>
                    @elseif($trf->transfer_status === 'cancelled')
                        <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px]">Cancelled</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl font-medium">
                    <div>
                        <span class="text-[9px] uppercase font-semibold text-slate-400 block">Gudang Asal</span>
                        <span class="text-slate-800 truncate block">[{{ $trf->originWarehouse->region->region_alias ?? 'N/A' }}] {{ $trf->originWarehouse->warehouse_name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase font-semibold text-slate-400 block">Gudang Tujuan</span>
                        <span class="text-slate-800 truncate block">[{{ $trf->destinationWarehouse->region->region_alias ?? 'N/A' }}] {{ $trf->destinationWarehouse->warehouse_name ?? '-' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-slate-500 font-medium">
                    <span>Total SKU: <strong class="text-slate-800">{{ $trf->items->count() }} Item</strong></span>
                </div>

                <!-- Aksi Tombol HP -->
                <div class="flex flex-wrap gap-2 pt-1">
                    <a href="{{ route('transfer.print', $trf->id) }}" target="_blank" class="flex-1 bg-indigo-50 text-indigo-600 active:bg-indigo-100 py-2.5 rounded-xl font-semibold transition text-center">
                        🖨️ Print
                    </a>
                    <button @click="selectedTransfer = {{ json_encode($trf) }}; showDetailModal = true;" class="flex-1 bg-slate-100 text-slate-700 active:bg-slate-200 py-2.5 rounded-xl font-semibold transition text-center">
                        Detail
                    </button>

                    @if($trf->transfer_status === 'draft')
                        <form action="{{ route('transfer.update-status', $trf->id) }}" method="POST" class="w-full" onsubmit="return confirm('Kirim transfer ini?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="transfer_status" value="in_transit">
                            <button type="submit" class="w-full bg-amber-50 text-amber-700 active:bg-amber-100 py-2.5 rounded-xl font-bold transition text-center">
                                Kirim (In Transit)
                            </button>
                        </form>
                    @endif

                    @if($trf->transfer_status === 'in_transit')
                        <form action="{{ route('transfer.update-status', $trf->id) }}" method="POST" class="w-full" onsubmit="return confirm('Selesaikan transfer ini?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="transfer_status" value="completed">
                            <button type="submit" class="w-full bg-emerald-50 text-emerald-700 active:bg-emerald-100 py-2.5 rounded-xl font-bold transition text-center">
                                Terima & Complete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">Belum ada dokumen transfer stok.</div>
            @endforelse
        </div>

        @if($transfers->hasPages())
            <div class="p-3 md:p-4 border-t border-slate-100 bg-slate-50">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah Transfer -->
    <div x-show="showModal" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="p-4 md:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm md:text-base">Buat Stock Transfer Baru</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Item yang sudah dipilih akan dinonaktifkan pada baris lainnya.</p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none font-bold px-2">&times;</button>
            </div>

            <form action="{{ route('transfer.store') }}" method="POST" class="p-4 md:p-6 space-y-4 overflow-y-auto flex-1 text-xs">
                @csrf
                <!-- Grid Gudang Asal & Gudang Tujuan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Gudang Asal (Origin) <span class="text-rose-500">*</span></label>
                        <select name="warehouse_origin_id" x-model="originWarehouseId" @change="updateStockMap(); if(destinationWarehouseId == originWarehouseId) destinationWarehouseId = '';" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                            <option value="" disabled selected>-- Pilih Gudang Asal --</option>
                            @foreach($userOriginWarehouses as $wh)
                                <option value="{{ $wh->id }}">[{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Gudang Tujuan (Destination) <span class="text-rose-500">*</span></label>
                        <select name="warehouse_destination_id" x-model="destinationWarehouseId" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                            <option value="" disabled selected>-- Pilih Gudang Tujuan --</option>
                            @foreach($warehouses as $wh)
                                <template x-if="originWarehouseId != {{ $wh->id }}">
                                    <option value="{{ $wh->id }}">[{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}</option>
                                </template>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Dynamic Items -->
                <div class="border-t border-slate-100 pt-3">
                    <div class="flex justify-between items-center mb-2">
                        <label class="font-bold text-slate-700 uppercase tracking-wider">Daftar Barang & Qty Transfer <span class="text-rose-500">*</span></label>
                        <button type="button" @click="itemsList.push({ item_id: '', qty_sent: 1, current_stock: 0 })" class="text-indigo-600 font-bold hover:underline">+ Tambah Barang</button>
                    </div>

                    <template x-for="(row, index) in itemsList" :key="index">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3 bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                            <!-- Select Item -->
                            <div class="flex-1 min-w-0">
                                <select :name="'items[' + index + '][item_id]'" 
                                        x-model="row.item_id" 
                                        @change="onItemChange(index)" 
                                        required 
                                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-base md:text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="" disabled>-- Pilih Item --</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" 
                                                :disabled="itemsList.some((it, i) => i !== index && it.item_id == '{{ $item->id }}')"
                                                :class="itemsList.some((it, i) => i !== index && it.item_id == '{{ $item->id }}') ? 'bg-slate-100 text-slate-400 font-normal' : ''">
                                            [{{ $item->item_sku }}] {{ $item->item_name }} {{ $item->item_variant ? "({$item->item_variant})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 justify-between sm:justify-start">
                                <!-- Info Stok Gudang Asal Saat Ini -->
                                <div class="text-center px-2.5 py-1 bg-white border border-slate-200 rounded-lg">
                                    <span class="text-[9px] text-slate-400 block font-semibold uppercase">Qty Now</span>
                                    <span class="font-bold font-mono text-xs" :class="row.current_stock > 0 ? 'text-emerald-600' : 'text-rose-500'" x-text="row.current_stock"></span>
                                </div>

                                <!-- Input Qty Transfer -->
                                <div>
                                    <input type="number" 
                                           :name="'items[' + index + '][qty_sent]'" 
                                           x-model="row.qty_sent" 
                                           min="1" 
                                           required 
                                           placeholder="Qty" 
                                           class="w-20 px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-base md:text-xs text-center font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <!-- Button Remove -->
                                <button type="button" @click="itemsList.splice(index, 1)" x-show="itemsList.length > 1" class="text-rose-500 font-bold px-2 text-xl leading-none">&times;</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Catatan Transfer (Optional)</label>
                    <textarea name="transfer_notes" rows="2" placeholder="Catatan tambahan alokasi/instruksi transfer..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <!-- Modal Footer -->
                <div class="pt-2 flex gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition">Simpan Document</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Transfer -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showDetailModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-4 md:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm md:text-base" x-text="selectedTransfer ? selectedTransfer.transfer_id : ''"></h3>
                    <p class="text-xs text-slate-400 mt-0.5">Detail Dokumen Transfer Inter-Warehouse</p>
                </div>
                <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none font-bold px-2">&times;</button>
            </div>

            <div class="p-4 md:p-6 space-y-4 overflow-y-auto flex-1 text-xs text-slate-700" x-if="selectedTransfer">
                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-xl">
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[9px] block">Gudang Asal</span>
                        <span class="font-bold text-slate-800" x-text="selectedTransfer?.origin_warehouse?.warehouse_name"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[9px] block">Gudang Tujuan</span>
                        <span class="font-bold text-slate-800" x-text="selectedTransfer?.destination_warehouse?.warehouse_name"></span>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-600 uppercase tracking-wider mb-2">Item Transfer</h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 font-semibold text-slate-500 uppercase">
                                <tr>
                                    <th class="p-3">SKU / Item</th>
                                    <th class="p-3 text-center">Qty Sent</th>
                                    <th class="p-3 text-center">Qty Received</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <template x-for="item in selectedTransfer?.items" :key="item.id">
                                    <tr>
                                        <td class="p-3">
                                            <div class="font-bold text-slate-800" x-text="item.item_name"></div>
                                            <div class="text-[10px] font-mono text-slate-400" x-text="item.item_sku + (item.item_sku_variant ? ' / ' + item.item_sku_variant : '')"></div>
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
                    <div>
                        <span class="font-bold text-slate-600 uppercase tracking-wider block mb-1">Catatan:</span>
                        <p class="text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 font-medium" x-text="selectedTransfer.transfer_notes"></p>
                    </div>
                </template>
            </div>

            <div class="p-4 border-t border-slate-100 flex justify-end shrink-0">
                <button type="button" @click="showDetailModal = false" class="px-4 py-2.5 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
</div>
@endsection