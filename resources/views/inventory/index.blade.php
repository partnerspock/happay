@extends('layouts.app')

@section('title', 'Inventory Movement - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;" 
     x-data="{ 
         showModal: false, 
         showDetailModal: false,
         selectedInventory: null,
         warehouseId: '',
         itemsList: [{ item_id: '', inventory_qty_sent: 1, current_stock: 0 }],
         stockMap: {},
         
         updateStockMap() {
             if (!this.warehouseId) return;
             fetch('/api/warehouse-stocks?warehouse_id=' + this.warehouseId)
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
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">Inventory Movement</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola pergerakan barang masuk (Inbound) & barang keluar (Outbound) external.</p>
        </div>
        <button @click="showModal = true" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center space-x-2 shrink-0">
            <span>+ Buat Pergerakan Stok</span>
        </button>
    </div>

    <!-- Filter Bar Component -->
    <div class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('inventory.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-center">
            
            <!-- Search Box -->
            <div class="min-w-0">
                <input type="text" name="search" value="{{ $search ?? request('search', '') }}" placeholder="Cari ID, Sender, Recipient..." 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Filter Warehouse -->
            <div class="min-w-0">
                <select name="warehouse_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Warehouse</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ ($selectedWarehouse ?? request('warehouse_id')) == $wh->id ? 'selected' : '' }}>
                            [{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Type -->
            <div class="min-w-0">
                <select name="inventory_type" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Tipe (In/Out)</option>
                    <option value="inbound" {{ ($selectedType ?? request('inventory_type')) === 'inbound' ? 'selected' : '' }}>📥 Inbound</option>
                    <option value="outbound" {{ ($selectedType ?? request('inventory_type')) === 'outbound' ? 'selected' : '' }}>📤 Outbound</option>
                </select>
            </div>

            <!-- Filter Status -->
            <div class="min-w-0">
                <select name="inventory_status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="created" {{ ($selectedStatus ?? request('inventory_status')) === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="arrived" {{ ($selectedStatus ?? request('inventory_status')) === 'arrived' ? 'selected' : '' }}>Arrived</option>
                    <option value="completed" {{ ($selectedStatus ?? request('inventory_status')) === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <!-- Action Filter & Reset -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white py-3 md:py-2.5 rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                @if(request('search') || request('inventory_type') || request('inventory_status') || request('warehouse_id'))
                    <a href="{{ route('inventory.index') }}" class="px-4 md:px-3 py-3 md:py-2.5 bg-slate-100 hover:bg-slate-200 text-rose-500 hover:text-rose-700 rounded-xl flex items-center justify-center font-bold text-xs transition" title="Reset Filter">
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
                        <th class="py-3.5 px-6">Inventory ID & Tipe</th>
                        <th class="py-3.5 px-6">Warehouse</th>
                        <th class="py-3.5 px-6">Pengirim & Kontak</th>
                        <th class="py-3.5 px-6">Penerima & Logistik</th>
                        <th class="py-3.5 px-6">Total SKU</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($inventories as $inv)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6">
                            <span class="text-slate-900 font-mono font-bold bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                {{ $inv->inventory_id }}
                            </span>
                            @if($inv->inventory_type === 'inbound')
                                <span class="block text-[10px] font-bold text-emerald-600 uppercase tracking-wider mt-1">📥 Inbound</span>
                            @else
                                <span class="block text-[10px] font-bold text-amber-600 uppercase tracking-wider mt-1">📤 Outbound</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-900">
                            [{{ $inv->warehouse->region->region_alias ?? 'N/A' }}] {{ $inv->warehouse->warehouse_name ?? '-' }}
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            <div class="font-semibold text-slate-800">{{ $inv->inventory_sender_name ?? '-' }}</div>
                            <div class="text-slate-400 font-mono text-[11px]">{{ $inv->inventory_sender_contact ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            <div class="font-semibold text-slate-800">{{ $inv->inventory_sender_recipient ?? '-' }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $inv->inventory_sender_logistic_info ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full font-bold">{{ $inv->items->count() }} Item</span>
                        </td>
                        <td class="py-4 px-6">
                            @if($inv->inventory_status === 'created')
                                <span class="bg-slate-100 text-slate-700 border border-slate-200 px-2.5 py-0.5 rounded-full font-semibold">Created</span>
                            @elseif($inv->inventory_status === 'arrived')
                                <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full font-semibold">Arrived</span>
                            @else
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold">Completed</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                            <!-- Print Button -->
                            <a href="{{ route('inventory.print', $inv->id) }}" target="_blank" 
                               class="font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition inline-flex items-center space-x-1">
                                <span>🖨️ Print</span>
                            </a>

                            <!-- Detail Button -->
                            <button @click="selectedInventory = {{ json_encode($inv) }}; showDetailModal = true;" 
                                    class="font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg transition">
                                Detail
                            </button>

                            @if($inv->inventory_status === 'created')
                                <form action="{{ route('inventory.update-status', $inv->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="inventory_status" value="arrived">
                                    <button type="submit" class="font-bold text-amber-600 hover:text-amber-800 bg-amber-50 px-3 py-1.5 rounded-lg transition">
                                        Arrived
                                    </button>
                                </form>
                            @endif

                            @if($inv->inventory_status !== 'completed')
                                <form action="{{ route('inventory.update-status', $inv->id) }}" method="POST" class="inline" onsubmit="return confirm('Selesaikan dokumen pergerakan stok ini? Stok akan otomatis diperbarui.')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="inventory_status" value="completed">
                                    <button type="submit" class="font-bold text-emerald-600 hover:text-emerald-800 bg-emerald-50 px-3 py-1.5 rounded-lg transition">
                                        Complete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 text-sm">Belum ada dokumen pergerakan stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100 text-xs">
            @forelse($inventories as $inv)
            <div class="p-3.5 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-900 font-mono font-bold bg-slate-100 px-2 py-0.5 rounded text-[11px]">
                        {{ $inv->inventory_id }}
                    </span>
                    @if($inv->inventory_type === 'inbound')
                        <span class="font-bold text-emerald-600 text-[10px]">📥 INBOUND</span>
                    @else
                        <span class="font-bold text-amber-600 text-[10px]">📤 OUTBOUND</span>
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900">[{{ $inv->warehouse->region->region_alias ?? 'N/A' }}] {{ $inv->warehouse->warehouse_name ?? '-' }}</span>
                    @if($inv->inventory_status === 'created')
                        <span class="bg-slate-100 text-slate-700 border border-slate-200 px-2 py-0.5 rounded-full font-semibold text-[10px]">Created</span>
                    @elseif($inv->inventory_status === 'arrived')
                        <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full font-semibold text-[10px]">Arrived</span>
                    @else
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-semibold text-[10px]">Completed</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl font-medium">
                    <div>
                        <span class="text-[9px] uppercase font-semibold text-slate-400 block">Pengirim</span>
                        <span class="text-slate-800 truncate block font-semibold">{{ $inv->inventory_sender_name ?? '-' }}</span>
                        <span class="text-slate-400 font-mono text-[10px] block">{{ $inv->inventory_sender_contact ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase font-semibold text-slate-400 block">Penerima & Logistik</span>
                        <span class="text-slate-800 truncate block font-semibold">{{ $inv->inventory_sender_recipient ?? '-' }}</span>
                        <span class="text-slate-400 text-[10px] block truncate">{{ $inv->inventory_sender_logistic_info ?? '-' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-slate-500 font-medium">
                    <span>Total SKU: <strong class="text-slate-800">{{ $inv->items->count() }} Item</strong></span>
                </div>

                <!-- Aksi Tombol HP -->
                <div class="flex flex-wrap gap-2 pt-1">
                    <a href="{{ route('inventory.print', $inv->id) }}" target="_blank" class="flex-1 bg-indigo-50 text-indigo-600 active:bg-indigo-100 py-2.5 rounded-xl font-semibold transition text-center">
                        🖨️ Print
                    </a>
                    <button @click="selectedInventory = {{ json_encode($inv) }}; showDetailModal = true;" class="flex-1 bg-slate-100 text-slate-700 active:bg-slate-200 py-2.5 rounded-xl font-semibold transition text-center">
                        Detail
                    </button>

                    @if($inv->inventory_status === 'created')
                        <form action="{{ route('inventory.update-status', $inv->id) }}" method="POST" class="w-full">
                            @csrf @method('PATCH')
                            <input type="hidden" name="inventory_status" value="arrived">
                            <button type="submit" class="w-full bg-amber-50 text-amber-700 active:bg-amber-100 py-2.5 rounded-xl font-bold transition text-center">
                                Set Arrived
                            </button>
                        </form>
                    @endif

                    @if($inv->inventory_status !== 'completed')
                        <form action="{{ route('inventory.update-status', $inv->id) }}" method="POST" class="w-full" onsubmit="return confirm('Selesaikan dokumen pergerakan stok ini?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="inventory_status" value="completed">
                            <button type="submit" class="w-full bg-emerald-50 text-emerald-700 active:bg-emerald-100 py-2.5 rounded-xl font-bold transition text-center">
                                Complete & Push Stock
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">Belum ada dokumen pergerakan stok.</div>
            @endforelse
        </div>

        @if($inventories->hasPages())
            <div class="p-3 md:p-4 border-t border-slate-100 bg-slate-50">
                {{ $inventories->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah Pergerakan -->
    <div x-show="showModal" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <div class="p-4 md:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm md:text-base">Buat Pergerakan Inventory Baru</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Warehouse otomatis terkunci sesuai region akun login Anda.</p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none font-bold px-2">&times;</button>
            </div>

            <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" class="p-4 md:p-6 space-y-4 overflow-y-auto flex-1 text-xs">
                @csrf
                <!-- Grid Warehouse & Tipe -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Pilih Warehouse <span class="text-rose-500">*</span></label>
                        <select name="warehouse_id" x-model="warehouseId" @change="updateStockMap()" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                            <option value="" disabled selected>-- Pilih Warehouse Region Anda --</option>
                            @foreach($warehouses as $wh)
                                @if(Auth::user()->region_id && $wh->warehouse_region == Auth::user()->region_id)
                                    <option value="{{ $wh->id }}">[{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}</option>
                                @elseif(!Auth::user()->region_id)
                                    <option value="{{ $wh->id }}">[{{ $wh->region->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Tipe Pergerakan <span class="text-rose-500">*</span></label>
                        <select name="inventory_type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                            <option value="inbound">📥 Inbound (Barang Masuk)</option>
                            <option value="outbound">📤 Outbound (Barang Keluar)</option>
                        </select>
                    </div>
                </div>

                <!-- Grid Sender Name & Sender Contact -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Nama Pengirim <span class="text-rose-500">*</span></label>
                        <input type="text" name="inventory_sender_name" required placeholder="EX: PT. Supplier Maju" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Kontak Pengirim <span class="text-rose-500">*</span></label>
                        <input type="text" name="inventory_sender_contact" required placeholder="EX: 081234567890" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Grid Logistic Info & Recipient -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Info Logistik / Kurir <span class="text-rose-500">*</span></label>
                        <input type="text" name="inventory_sender_logistic_info" required placeholder="EX: JNE - Resi 12345" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Nama Penerima <span class="text-rose-500">*</span></label>
                        <input type="text" name="inventory_sender_recipient" required placeholder="EX: Budi (Admin Gudang)" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Section Items Dynamic -->
                <div class="border-t border-slate-100 pt-3">
                    <div class="flex justify-between items-center mb-2">
                        <label class="font-bold text-slate-700 uppercase tracking-wider">Daftar Barang & Qty Surat Jalan <span class="text-rose-500">*</span></label>
                        <button type="button" @click="itemsList.push({ item_id: '', inventory_qty_sent: 1, current_stock: 0 })" class="text-indigo-600 font-bold hover:underline">+ Tambah Barang</button>
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
                                <!-- Info Stok Gudang Saat Ini -->
                                <div class="text-center px-2.5 py-1 bg-white border border-slate-200 rounded-lg">
                                    <span class="text-[9px] text-slate-400 block font-semibold uppercase">Qty Now</span>
                                    <span class="font-bold font-mono text-xs" :class="row.current_stock > 0 ? 'text-emerald-600' : 'text-rose-500'" x-text="row.current_stock"></span>
                                </div>

                                <!-- Input Qty Sent -->
                                <div>
                                    <input type="number" 
                                           :name="'items[' + index + '][inventory_qty_sent]'" 
                                           x-model="row.inventory_qty_sent" 
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

                <!-- Media Bukti (Optional) -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Foto / Bukti Surat Jalan (Optional)</label>
                    <input type="file" name="inventory_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <!-- Catatan (Optional) -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Catatan (Optional)</label>
                    <textarea name="inventory_notes" rows="2" placeholder="Catatan tambahan pergerakan stok..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <!-- Modal Footer -->
                <div class="pt-2 flex gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition">Simpan Dokumen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Inventory -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showDetailModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-4 md:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm md:text-base" x-text="selectedInventory ? selectedInventory.inventory_id : ''"></h3>
                    <p class="text-xs text-slate-400 mt-0.5">Detail Dokumen Inventory Movement</p>
                </div>
                <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none font-bold px-2">&times;</button>
            </div>

            <div class="p-4 md:p-6 space-y-4 overflow-y-auto flex-1 text-xs text-slate-700" x-if="selectedInventory">
                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-xl">
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[9px] block">Pengirim</span>
                        <span class="font-bold text-slate-800" x-text="selectedInventory?.inventory_sender_name"></span>
                        <span class="block text-slate-500 font-mono text-[10px]" x-text="selectedInventory?.inventory_sender_contact"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold uppercase text-[9px] block">Penerima & Logistik</span>
                        <span class="font-bold text-slate-800" x-text="selectedInventory?.inventory_sender_recipient"></span>
                        <span class="block text-slate-500 text-[10px]" x-text="selectedInventory?.inventory_sender_logistic_info"></span>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-600 uppercase tracking-wider mb-2">Item Dalam Dokumen</h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 font-semibold text-slate-500 uppercase">
                                <tr>
                                    <th class="p-3">SKU / Item</th>
                                    <th class="p-3 text-center">Qty Sent</th>
                                    <th class="p-3 text-center">Qty Change</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <template x-for="item in selectedInventory?.items" :key="item.id">
                                    <tr>
                                        <td class="p-3">
                                            <div class="font-bold text-slate-800" x-text="item.item_name"></div>
                                            <div class="text-[10px] font-mono text-slate-400" x-text="item.item_sku + (item.item_sku_variant ? ' / ' + item.item_sku_variant : '')"></div>
                                        </td>
                                        <td class="p-3 text-center font-mono font-bold" x-text="item.inventory_qty_sent"></td>
                                        <td class="p-3 text-center font-mono font-bold text-indigo-600" x-text="item.inventory_qty_change"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <template x-if="selectedInventory?.inventory_notes">
                    <div>
                        <span class="font-bold text-slate-600 uppercase tracking-wider block mb-1">Catatan:</span>
                        <p class="text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 font-medium" x-text="selectedInventory.inventory_notes"></p>
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