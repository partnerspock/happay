@extends('layouts.app')

@section('title', 'Master Item - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;" 
     x-data="{ 
         showModal: {{ $errors->has('item_sku') || $errors->has('item_name') ? 'true' : 'false' }}, 
         isEdit: false, 
         isVariant: false,
         formUrl: '{{ route('items.store') }}', 
         id: '', 
         sku: '{{ old('item_sku') }}', 
         skuVariant: '{{ old('item_sku_variant') }}', 
         name: '{{ old('item_name') }}', 
         variant: '{{ old('item_variant') }}', 
         price: '{{ old('item_price', 0) }}', 
         cogs: '{{ old('item_cogs', 0) }}', 
         lifetime: '{{ old('item_lifetime') }}', 
         status: '{{ old('item_status', 'available') }}' 
     }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">Master Item</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola katalog produk, varian, gambar, HPP, dan harga jual.</p>
        </div>
        <button @click="showModal = true; isEdit = false; isVariant = false; formUrl = '{{ route('items.store') }}'; sku = ''; skuVariant = ''; name = ''; variant = ''; price = 0; cogs = 0; lifetime = ''; status = 'available';" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center space-x-2 shrink-0">
            <span>+ Tambah Item</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
            <span>Filter Status:</span>
            <form action="{{ route('items.index') }}" method="GET" class="flex items-center space-x-2">
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none text-xs font-medium">
                    <option value="">Semua Status</option>
                    <option value="available" {{ ($statusFilter ?? '') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="hold" {{ ($statusFilter ?? '') === 'hold' ? 'selected' : '' }}>Hold</option>
                    <option value="discontinue" {{ ($statusFilter ?? '') === 'discontinue' ? 'selected' : '' }}>Discontinue</option>
                </select>
                @if(!empty($statusFilter))
                    <a href="{{ route('items.index') }}" class="text-rose-500 hover:text-rose-700 text-xs font-bold underline">Reset Filter</a>
                @endif
            </form>
        </div>

        <div class="text-xs text-slate-400 font-medium">
            Total Item: <span class="font-bold text-slate-700">{{ $items->count() }}</span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        {{-- ============ TABEL (iPad & desktop, >= 768px) ============ --}}
        <div class="hidden md:block overflow-x-auto max-h-[65vh]">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 bg-slate-50/80 border-b border-slate-200/80 text-xs text-slate-500 uppercase tracking-wider font-semibold z-10">
                    <tr>
                        <th class="py-3.5 px-6">Gambar</th>
                        <th class="py-3.5 px-6">SKU / Variant SKU</th>
                        <th class="py-3.5 px-6">Item Name</th>
                        <th class="py-3.5 px-6">Variant</th>
                        <th class="py-3.5 px-6">HPP (COGS)</th>
                        <th class="py-3.5 px-6">Harga Jual</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6 flex items-center space-x-2">
                            @if($item->item_image)
                                <img src="{{ asset('storage/' . $item->item_image) }}" alt="Model" class="w-9 h-9 rounded-lg object-cover border border-slate-200 shadow-sm" title="Model Image">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200">No Model</div>
                            @endif

                            @if($item->item_variant_image)
                                <img src="{{ asset('storage/' . $item->item_variant_image) }}" alt="Variant" class="w-9 h-9 rounded-lg object-cover border border-slate-200 shadow-sm" title="Variant Image">
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-mono font-bold">{{ $item->item_sku }}</span>
                            @if($item->item_sku_variant)
                                <span class="block text-[11px] font-mono text-slate-400 mt-1">{{ $item->item_sku_variant }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-900">{{ $item->item_name }}</td>
                        <td class="py-4 px-6 text-slate-500">{{ $item->item_variant ?? '-' }}</td>
                        <td class="py-4 px-6 font-mono text-slate-600">Rp {{ number_format($item->item_cogs, 0, ',', '.') }}</td>
                        <td class="py-4 px-6 font-mono font-bold text-indigo-600">Rp {{ number_format($item->item_price, 0, ',', '.') }}</td>
                        <td class="py-4 px-6">
                            @if($item->item_status === 'available')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold">Available</span>
                            @elseif($item->item_status === 'hold')
                                <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full font-semibold">Hold</span>
                            @else
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold">Discontinue</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                            <!-- Button Add Variant -->
                            <button @click="showModal = true; isEdit = false; isVariant = true; formUrl = '{{ route('items.store') }}'; id = ''; sku = '{{ $item->item_sku }}'; skuVariant = '{{ $item->item_sku }}-VAR'; name = '{{ $item->item_name }}'; variant = ''; price = '{{ $item->item_price }}'; cogs = '{{ $item->item_cogs }}'; lifetime = ''; status = 'available';" 
                                    class="font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg transition"
                                    title="Tambah varian baru untuk item ini">
                                + Variant
                            </button>

                            <!-- Button Edit -->
                            <button @click="showModal = true; isEdit = true; isVariant = false; formUrl = '/items/{{ $item->id }}'; id = '{{ $item->id }}'; sku = '{{ $item->item_sku }}'; skuVariant = '{{ $item->item_sku_variant }}'; name = '{{ $item->item_name }}'; variant = '{{ $item->item_variant }}'; price = '{{ $item->item_price }}'; cogs = '{{ $item->item_cogs }}'; lifetime = '{{ $item->item_lifetime ? \Carbon\Carbon::parse($item->item_lifetime)->format('Y-m-d\TH:i') : '' }}'; status = '{{ $item->item_status }}';" 
                                    class="font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400 text-sm">Belum ada data barang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100 text-xs">
            @forelse($items as $item)
            <div class="p-3.5 space-y-3">
                <!-- Baris Atas: Gambar & Status -->
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center space-x-2.5">
                        @if($item->item_image)
                            <img src="{{ asset('storage/' . $item->item_image) }}" alt="Model" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shadow-sm">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200">No Img</div>
                        @endif

                        @if($item->item_variant_image)
                            <img src="{{ asset('storage/' . $item->item_variant_image) }}" alt="Variant" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shadow-sm">
                        @endif

                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ $item->item_name }}</div>
                            <div class="text-slate-500 text-[11px]">{{ $item->item_variant ?? 'Standard' }}</div>
                        </div>
                    </div>

                    @if($item->item_status === 'available')
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px] shrink-0">Available</span>
                    @elseif($item->item_status === 'hold')
                        <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px] shrink-0">Hold</span>
                    @else
                        <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px] shrink-0">Discontinue</span>
                    @endif
                </div>

                <!-- Info SKU & Harga -->
                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl font-mono">
                    <div>
                        <div class="text-[9px] uppercase font-semibold text-slate-400">SKU</div>
                        <div class="font-bold text-slate-700 truncate">{{ $item->item_sku }}</div>
                        @if($item->item_sku_variant)
                            <div class="text-[10px] text-slate-400 truncate">{{ $item->item_sku_variant }}</div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] uppercase font-semibold text-slate-400">Harga Jual</div>
                        <div class="font-bold text-indigo-600">Rp {{ number_format($item->item_price, 0, ',', '.') }}</div>
                        <div class="text-[10px] text-slate-400 font-normal">HPP: Rp {{ number_format($item->item_cogs, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Aksi Tombol HP -->
                <div class="flex gap-2 pt-1">
                    <button @click="showModal = true; isEdit = false; isVariant = true; formUrl = '{{ route('items.store') }}'; id = ''; sku = '{{ $item->item_sku }}'; skuVariant = '{{ $item->item_sku }}-VAR'; name = '{{ $item->item_name }}'; variant = ''; price = '{{ $item->item_price }}'; cogs = '{{ $item->item_cogs }}'; lifetime = ''; status = 'available';" 
                            class="flex-1 bg-emerald-50 text-emerald-700 active:bg-emerald-100 py-2.5 rounded-xl font-semibold transition text-center">
                        + Variant
                    </button>
                    <button @click="showModal = true; isEdit = true; isVariant = false; formUrl = '/items/{{ $item->id }}'; id = '{{ $item->id }}'; sku = '{{ $item->item_sku }}'; skuVariant = '{{ $item->item_sku_variant }}'; name = '{{ $item->item_name }}'; variant = '{{ $item->item_variant }}'; price = '{{ $item->item_price }}'; cogs = '{{ $item->item_cogs }}'; lifetime = '{{ $item->item_lifetime ? \Carbon\Carbon::parse($item->item_lifetime)->format('Y-m-d\TH:i') : '' }}'; status = '{{ $item->item_status }}';" 
                            class="flex-1 bg-indigo-50 text-indigo-600 active:bg-indigo-100 py-2.5 rounded-xl font-semibold transition text-center">
                        Edit Item
                    </button>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">Belum ada data barang.</div>
            @endforelse
        </div>

    </div>

    <!-- Modal Form (Create, Edit & Add Variant) with enctype multipart -->
    <div x-show="showModal" 
         x-transition 
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" 
         style="display: none;">
        
        <div @click.away="showModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-lg overflow-hidden flex flex-col" style="max-height: 92vh; max-height: 92dvh;">
            
            <!-- Modal Header -->
            <div class="p-4 md:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h3 class="font-bold text-slate-900 text-sm md:text-base" 
                    x-text="isEdit ? 'Edit Item' : (isVariant ? 'Tambah Varian Baru' : 'Tambah Item Baru')"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none font-bold px-2">&times;</button>
            </div>

            <!-- Modal Body (Scrollable) with enctype -->
            <form :action="formUrl" method="POST" enctype="multipart/form-data" class="p-4 md:p-6 space-y-4 overflow-y-auto flex-1 text-xs">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Hidden Input jika Item Name & SKU dikunci saat Mode Variant -->
                <template x-if="isVariant">
                    <div>
                        <input type="hidden" name="item_name" :value="name">
                        <input type="hidden" name="item_sku" :value="sku">
                    </div>
                </template>

                <!-- Grid SKU & SKU Variant -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Item SKU</label>
                        <input type="text" 
                               name="item_sku" 
                               x-model="sku" 
                               :required="!isEdit && !isVariant"
                               :disabled="isEdit || isVariant"
                               placeholder="EX: SKU-001" 
                               :class="(isEdit || isVariant) ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200 font-mono font-bold' : 'bg-slate-50 border-slate-200 text-slate-800 focus:ring-2 focus:ring-indigo-500'"
                               class="w-full px-4 py-2.5 rounded-xl border focus:outline-none uppercase font-mono text-base md:text-xs transition">
                        <template x-if="isVariant">
                            <p class="text-[10px] text-slate-400 mt-1">*Induk SKU terkunci.</p>
                        </template>
                        @if($errors->has('item_sku'))
                            <p class="text-[11px] text-rose-500 font-medium mt-1">* {{ $errors->first('item_sku') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">
                            SKU Variant <span x-show="isVariant" class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="item_sku_variant" 
                               x-model="skuVariant" 
                               :required="isVariant"
                               placeholder="EX: SKU-001-RED" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-base md:text-xs">
                        
                        @if($errors->has('item_sku_variant'))
                            <p class="text-[11px] text-rose-500 font-medium mt-1">* {{ $errors->first('item_sku_variant') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Grid Item Name & Variant Name -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Item Name</label>
                        <input type="text" 
                               name="item_name" 
                               x-model="name" 
                               :required="!isVariant" 
                               :disabled="isVariant"
                               placeholder="EX: Kopi Susu Aren" 
                               :class="isVariant ? 'bg-slate-100 text-slate-500 cursor-not-allowed border-slate-200 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-800 focus:ring-2 focus:ring-indigo-500'"
                               class="w-full px-4 py-2.5 rounded-xl border focus:outline-none text-base md:text-xs transition">
                        <template x-if="isVariant">
                            <p class="text-[10px] text-slate-400 mt-1">*Terkunci dari induk item.</p>
                        </template>
                        @if($errors->has('item_name'))
                            <p class="text-[11px] text-rose-500 font-medium mt-1">* {{ $errors->first('item_name') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">
                            Variant Name <span x-show="isVariant" class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="item_variant" 
                               x-model="variant" 
                               :required="isVariant"
                               placeholder="EX: Less Sugar / 250ml" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs">
                    </div>
                </div>

                <!-- Upload Images -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Model Image <span class="text-rose-500">*</span></label>
                        <input type="file" name="item_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @if($errors->has('item_image'))
                            <p class="text-[11px] text-rose-500 font-medium mt-1">* {{ $errors->first('item_image') }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Variant Image (Opsional)</label>
                        <input type="file" name="item_variant_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
                    </div>
                </div>

                <!-- Grid COGS & Price -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">HPP / COGS (Rp)</label>
                        <input type="number" 
                               name="item_cogs" 
                               x-model="cogs" 
                               required 
                               min="0" 
                               step="100" 
                               placeholder="10000" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-base md:text-xs">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Harga Jual (Rp)</label>
                        <input type="number" 
                               name="item_price" 
                               x-model="price" 
                               required 
                               min="0" 
                               step="100" 
                               placeholder="18000" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-base md:text-xs">
                    </div>
                </div>

                <!-- Lifetime & Status -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Lifetime / Expired</label>
                        <input type="datetime-local" 
                               name="item_lifetime" 
                               x-model="lifetime" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Status</label>
                        <select name="item_status" x-model="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs">
                            <option value="available">Available</option>
                            <option value="hold">Hold</option>
                            <option value="discontinue">Discontinue</option>
                        </select>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="pt-2 flex gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition" 
                            x-text="isEdit ? 'Simpan Perubahan' : (isVariant ? 'Tambah Varian' : 'Tambah Item')">
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
@endsection