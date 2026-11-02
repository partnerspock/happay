@extends('layouts.app')

@section('title', 'Inventory Movement - Happay')

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
<div class="w-full h-full overflow-hidden bg-slate-100 app-content-zoom" x-data="inventoryPage()">
    <div class="h-full overflow-y-auto custom-scrollbar" style="-webkit-overflow-scrolling: touch;">

        <div class="p-3.5 sm:p-5 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-20 sm:pb-12">

            {{-- HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m-6 3.75 3 3m0 0 3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">Inventory Movement</h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">Pencatatan mutasi <span class="text-emerald-600 font-semibold">Inbound</span> &amp; <span class="text-amber-600 font-semibold">Outbound</span> gudang cabang.</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <button type="button" @click="openCreateModal()"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 active:scale-95 text-white px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-lg shadow-indigo-600/25 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Buat Pergerakan Stok</span>
                    </button>
                </div>
            </div>

            {{-- FLASH ALERTS --}}
            @if(session('success'))
            <div class="flex items-start gap-3 rounded-2xl bg-emerald-50 border border-emerald-200/90 p-4 text-emerald-800 text-xs sm:text-sm font-medium shadow-sm">
                <span class="font-bold">Berhasil!</span> {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="flex items-start gap-3 rounded-2xl bg-rose-50 border border-rose-200/90 p-4 text-rose-800 text-xs sm:text-sm font-medium shadow-sm">
                <span class="font-bold">Terjadi Kesalahan!</span> {{ session('error') }}
            </div>
            @endif

            {{-- FILTER --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form method="GET" action="{{ route('inventory.index') }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Cari Dokumen / Pihak</label>
                            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari ID, Nama Pengirim..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="lg:col-span-3">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Warehouse</label>
                            <select name="warehouse_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected((string)($selectedWarehouse ?? '') === (string)$warehouse->id)>
                                    [{{ $warehouse->region->region_alias ?? 'N/A' }}] {{ $warehouse->warehouse_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tipe</label>
                            <select name="inventory_type" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua Tipe</option>
                                <option value="inbound" @selected(($selectedType ?? '') === 'inbound')>Inbound</option>
                                <option value="outbound" @selected(($selectedType ?? '') === 'outbound')>Outbound</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Status</label>
                            <select name="inventory_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Semua Status</option>
                                <option value="created" @selected(($selectedStatus ?? '') === 'created')>Created</option>
                                <option value="arrived" @selected(($selectedStatus ?? '') === 'arrived')>Arrived</option>
                                <option value="completed" @selected(($selectedStatus ?? '') === 'completed')>Completed</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1 flex items-center gap-1.5">
                            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white h-[42px] px-2.5 rounded-xl font-bold text-xs transition flex items-center justify-center">Filter</button>
                            <a href="{{ route('inventory.index') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition">🔄</a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TABLE VIEW --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                            <tr>
                                <th class="py-3.5 px-4">Inventory ID & Waktu</th>
                                <th class="py-3.5 px-4">Warehouse</th>
                                <th class="py-3.5 px-3">Tipe</th>
                                <th class="py-3.5 px-4">Pengirim</th>
                                <th class="py-3.5 px-4">Penerima & Logistik</th>
                                <th class="py-3.5 px-3 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center">Lampiran</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($inventories as $inv)
                            <tr class="hover:bg-indigo-50/25 transition">
                                <td class="py-3.5 px-4">
                                    <span class="text-slate-900 font-mono font-bold bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200 text-xs">{{ $inv->inventory_id }}</span>
                                    <div class="text-[10px] text-slate-400 mt-1 font-mono">{{ optional($inv->created_at)->format('d M Y, H:i') }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800 text-xs">
                                    [{{ $inv->warehouse->region->region_alias ?? 'N/A' }}] {{ $inv->warehouse->warehouse_name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] {{ $inv->inventory_type === 'inbound' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ ucfirst($inv->inventory_type) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-xs">{{ $inv->inventory_sender_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $inv->inventory_sender_contact }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-xs">{{ $inv->inventory_sender_recipient }}</div>
                                    <div class="text-[10px] text-slate-500 truncate max-w-[150px]">{{ $inv->inventory_sender_logistic_info }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] bg-slate-100 text-slate-600">{{ ucfirst($inv->inventory_status) }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($inv->inventory_sender_image)
                                    <button type="button" @click="openMediaModal({{ \Illuminate\Support\Js::from($inv) }}, 'image')" class="text-indigo-600 font-bold underline">Foto</button>
                                    @endif
                                    @if($inv->inventory_sender_video)
                                    <button type="button" @click="openMediaModal({{ \Illuminate\Support\Js::from($inv) }}, 'video')" class="text-violet-600 font-bold underline ml-1">Video</button>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <button type="button" @click="openDetail({{ \Illuminate\Support\Js::from($inv) }})" class="bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg font-semibold">Detail</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">Tidak ada data inventory ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if(method_exists($inventories, 'hasPages') && $inventories->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/60">
                    {{ $inventories->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ================= MODAL CREATE INVENTORY ================= --}}
    <div x-cloak x-show="showCreateModal" x-transition.opacity
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4"
         @keydown.escape.window="showCreateModal = false">
        <div @click.outside="showCreateModal = false" 
             class="w-full sm:max-w-xl rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl overflow-hidden max-h-[92dvh] sm:max-h-[90vh] flex flex-col border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-indigo-50/80 via-white to-slate-50 p-4 sm:p-5 shrink-0">
                <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Buat Pergerakan Stok Baru</h3>
                <button type="button" @click="showCreateModal = false" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">&times;</button>
            </div>
            <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" class="p-4 sm:p-6 space-y-4 text-xs overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                <div>
                    <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">Warehouse <span class="text-rose-500">*</span></label>
                    <select name="warehouse_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Warehouse...</option>
                        @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">[{{ $warehouse->region->region_alias ?? 'N/A' }}] {{ $warehouse->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">Tipe Pergerakan <span class="text-rose-500">*</span></label>
                        <select name="inventory_type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="inbound">Inbound (Masuk)</option>
                            <option value="outbound">Outbound (Keluar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">Status <span class="text-rose-500">*</span></label>
                        <select name="inventory_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="created">Created</option>
                            <option value="arrived">Arrived</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">Nama Pengirim</label>
                        <input type="text" name="inventory_sender_name" placeholder="Nama pengirim..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">Kontak Pengirim</label>
                        <input type="text" name="inventory_sender_contact" placeholder="No Telp..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">Nama Penerima</label>
                        <input type="text" name="inventory_sender_recipient" placeholder="Nama penerima..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">Info Logistik</label>
                        <input type="text" name="inventory_sender_logistic_info" placeholder="Kurir / Resi..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                    <div>
                        <label class="block font-bold text-slate-700 text-[11px] mb-1">Foto Bukti</label>
                        <input type="file" name="inventory_sender_image" accept="image/*" class="w-full text-xs text-slate-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 text-[11px] mb-1">Video Bukti</label>
                        <input type="file" name="inventory_sender_video" accept="video/*" class="w-full text-xs text-slate-500">
                    </div>
                </div>
                <div class="flex gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="flex-1 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/25 transition">Simpan Pergerakan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL DETAIL INVENTORY ================= --}}
    <div x-cloak x-show="showDetailModal" x-transition.opacity
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4"
         @keydown.escape.window="showDetailModal = false">
        <div @click.outside="showDetailModal = false" 
             class="w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl overflow-hidden max-h-[92dvh] sm:max-h-[90vh] flex flex-col border border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-indigo-50/80 via-white to-slate-50 p-4 sm:p-5 shrink-0">
                <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">Detail Inventory Movement</h3>
                <button type="button" @click="showDetailModal = false" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">&times;</button>
            </div>
            <div class="p-4 sm:p-6 space-y-3 text-xs overflow-y-auto flex-1 custom-scrollbar">
                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Inventory ID</span>
                        <span class="font-mono font-bold text-indigo-600" x-text="activeInv.inventory_id"></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Tipe & Status</span>
                        <span class="font-bold uppercase" x-text="activeInv.inventory_type + ' / ' + activeInv.inventory_status"></span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div><strong class="text-slate-400 text-[10px] uppercase block">Pengirim</strong> <span class="text-slate-800 font-semibold" x-text="activeInv.inventory_sender_name + ' (' + (activeInv.inventory_sender_contact || '-') + ')'"></span></div>
                    <div><strong class="text-slate-400 text-[10px] uppercase block">Penerima</strong> <span class="text-slate-800 font-semibold" x-text="activeInv.inventory_sender_recipient"></span></div>
                    <div><strong class="text-slate-400 text-[10px] uppercase block">Info Logistik</strong> <span class="text-slate-800 font-semibold" x-text="activeInv.inventory_sender_logistic_info || '-'"></span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL MEDIA VIEWER ================= --}}
    <div x-cloak x-show="showMediaModal" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4"
         @keydown.escape.window="showMediaModal = false">
        <div @click.outside="showMediaModal = false" class="bg-white rounded-3xl p-4 max-w-lg w-full overflow-hidden shadow-2xl relative">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-bold text-slate-900 text-sm">Lampiran Bukti</h4>
                <button type="button" @click="showMediaModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
            </div>
            <div class="flex justify-center items-center bg-slate-100 rounded-2xl overflow-hidden min-h-[250px]">
                <template x-if="mediaType === 'image' && mediaUrl">
                    <img :src="mediaUrl" class="max-h-[60vh] object-contain rounded-xl">
                </template>
                <template x-if="mediaType === 'video' && mediaUrl">
                    <video :src="mediaUrl" controls class="max-h-[60vh] w-full rounded-xl"></video>
                </template>
            </div>
        </div>
    </div>

</div>

<script>
function inventoryPage() {
    return {
        showCreateModal: false,
        showDetailModal: false,
        showMediaModal: false,
        activeInv: {},
        mediaUrl: '',
        mediaType: 'image',

        openCreateModal() {
            this.showCreateModal = true;
        },

        openDetail(inv) {
            this.activeInv = inv;
            this.showDetailModal = true;
        },

        openMediaModal(inv, type) {
            this.mediaType = type;
            this.mediaUrl = type === 'image' ? ('/storage/' + inv.inventory_sender_image) : ('/storage/' + inv.inventory_sender_video);
            this.showMediaModal = true;
        }
    }
}
</script>
@endsection