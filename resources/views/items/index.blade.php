@extends('layouts.app')

@section('title', 'Master Item - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory, transfer, warehouse, history, audit & stocks standard */
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
    <div class="h-full overflow-y-auto custom-scrollbar" style="-webkit-overflow-scrolling: touch;">

        {{-- ===== PAGE SHELL ===== --}}
        <div class="p-3.5 sm:p-5 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-20 sm:pb-12">

            {{-- 1. TOP HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                Master Item
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Kelola katalog produk, varian, gambar, HPP, dan <span class="text-indigo-600 font-semibold">harga jual</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <button type="button" @click="showModal = true; isEdit = false; isVariant = false; formUrl = '{{ route('items.store') }}'; sku = ''; skuVariant = ''; name = ''; variant = ''; price = 0; cogs = 0; lifetime = ''; status = 'available';"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 active:scale-95 text-white px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-lg shadow-indigo-600/25 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Tambah Item</span>
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

            {{-- 2. FILTER & SEARCH COMPONENT (Fixed 12-col layout) --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form action="{{ route('items.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        
                        {{-- Status Filter (Col 5) --}}
                        <div class="sm:col-span-2 lg:col-span-5">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Filter Status Item
                            </label>
                            <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="">Semua Status</option>
                                <option value="available" {{ ($statusFilter ?? '') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="hold" {{ ($statusFilter ?? '') === 'hold' ? 'selected' : '' }}>Hold</option>
                                <option value="discontinue" {{ ($statusFilter ?? '') === 'discontinue' ? 'selected' : '' }}>Discontinue</option>
                            </select>
                        </div>

                        {{-- Total Info Badge (Col 4) --}}
                        <div class="lg:col-span-4 flex items-center py-2.5 px-3.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-xs text-slate-500 font-medium">Total Katalog: <strong class="text-slate-800 font-black">{{ $items->count() }} Item</strong></span>
                        </div>

                        {{-- Filter & Reset Buttons (Col 3) --}}
                        <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-1.5">
                            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white h-[42px] px-3 rounded-xl font-bold text-xs transition flex items-center justify-center shadow-sm">
                                <span>Filter</span>
                            </button>
                            @if(!empty($statusFilter))
                                <a href="{{ route('items.index') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition active:scale-95" title="Reset Filter">
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
                                <th class="py-3.5 px-4">Gambar</th>
                                <th class="py-3.5 px-4">SKU / Variant SKU</th>
                                <th class="py-3.5 px-4">Item Name</th>
                                <th class="py-3.5 px-4">Variant</th>
                                <th class="py-3.5 px-4">HPP (COGS)</th>
                                <th class="py-3.5 px-4">Harga Jual</th>
                                <th class="py-3.5 px-3 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($items as $item)
                            <tr class="hover:bg-indigo-50/25 transition group">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        @if($item->item_image)
                                            <img src="{{ asset('storage/' . $item->item_image) }}" alt="Model" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0" title="Model Image">
                                        @else
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200 shrink-0">No Img</div>
                                        @endif

                                        @if($item->item_variant_image)
                                            <img src="{{ asset('storage/' . $item->item_variant_image) }}" alt="Variant" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0" title="Variant Image">
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="bg-slate-100 group-hover:bg-white text-slate-900 px-2 py-0.5 rounded-lg font-mono font-bold border border-slate-200 text-xs">
                                        {{ $item->item_sku }}
                                    </span>
                                    @if($item->item_sku_variant)
                                        <div class="text-[10px] font-mono text-indigo-600 mt-1 font-semibold">{{ $item->item_sku_variant }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $item->item_name }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-medium">
                                    {{ $item->item_variant ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">
                                    Rp {{ number_format($item->item_cogs, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-extrabold text-indigo-600">
                                    Rp {{ number_format($item->item_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($item->item_status === 'available')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                            Available
                                        </span>
                                    @elseif($item->item_status === 'hold')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                                            Hold
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                            Discontinue
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button @click="showModal = true; isEdit = false; isVariant = true; formUrl = '{{ route('items.store') }}'; id = ''; sku = '{{ $item->item_sku }}'; skuVariant = '{{ $item->item_sku }}-VAR'; name = '{{ $item->item_name }}'; variant = ''; price = '{{ $item->item_price }}'; cogs = '{{ $item->item_cogs }}'; lifetime = ''; status = 'available';" 
                                                class="inline-flex items-center gap-1 font-bold text-emerald-800 bg-emerald-100/80 hover:bg-emerald-200 px-2.5 py-1.5 rounded-lg transition text-[11px]" title="Tambah varian baru">
                                            + Variant
                                        </button>

                                        <button @click="showModal = true; isEdit = true; isVariant = false; formUrl = '/items/{{ $item->id }}'; id = '{{ $item->id }}'; sku = '{{ $item->item_sku }}'; skuVariant = '{{ $item->item_sku_variant }}'; name = '{{ $item->item_name }}'; variant = '{{ $item->item_variant }}'; price = '{{ $item->item_price }}'; cogs = '{{ $item->item_cogs }}'; lifetime = '{{ $item->item_lifetime ? \Carbon\Carbon::parse($item->item_lifetime)->format('Y-m-d\TH:i') : '' }}'; status = '{{ $item->item_status }}';" 
                                                class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition text-[11px]">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Belum ada data barang.</p>
                                        <p class="text-slate-400 text-xs">Silakan tambahkan item baru ke katalog Happay.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($items as $item)
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                @if($item->item_image)
                                    <img src="{{ asset('storage/' . $item->item_image) }}" alt="Model" class="w-11 h-11 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                                @else
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-[9px] text-slate-400 font-bold border border-slate-200 shrink-0">No Img</div>
                                @endif

                                @if($item->item_variant_image)
                                    <img src="{{ asset('storage/' . $item->item_variant_image) }}" alt="Variant" class="w-11 h-11 rounded-xl object-cover border border-slate-200 shadow-sm shrink-0">
                                @endif

                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $item->item_name }}</div>
                                    <div class="text-slate-500 text-xs font-medium">{{ $item->item_variant ?? 'Standard' }}</div>
                                </div>
                            </div>

                            @if($item->item_status === 'available')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">Available</span>
                            @elseif($item->item_status === 'hold')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">Hold</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 shrink-0">Discontinue</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100 font-mono text-xs">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">SKU</span>
                                <span class="font-bold text-slate-800 truncate block">{{ $item->item_sku }}</span>
                                @if($item->item_sku_variant)
                                    <span class="text-[10px] text-indigo-600 truncate block">{{ $item->item_sku_variant }}</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Harga Jual</span>
                                <span class="font-black text-indigo-600">Rp {{ number_format($item->item_price, 0, ',', '.') }}</span>
                                <div class="text-[10px] text-slate-400 font-normal">HPP: Rp {{ number_format($item->item_cogs, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <!-- Aksi Tombol HP -->
                        <div class="flex gap-2 pt-1">
                            <button @click="showModal = true; isEdit = false; isVariant = true; formUrl = '{{ route('items.store') }}'; id = ''; sku = '{{ $item->item_sku }}'; skuVariant = '{{ $item->item_sku }}-VAR'; name = '{{ $item->item_name }}'; variant = ''; price = '{{ $item->item_price }}'; cogs = '{{ $item->item_cogs }}'; lifetime = ''; status = 'available';" 
                                    class="flex-1 bg-emerald-50 active:bg-emerald-100 text-emerald-800 py-2.5 rounded-xl font-bold text-xs transition text-center shadow-sm">
                                + Variant
                            </button>
                            <button @click="showModal = true; isEdit = true; isVariant = false; formUrl = '/items/{{ $item->id }}'; id = '{{ $item->id }}'; sku = '{{ $item->item_sku }}'; skuVariant = '{{ $item->item_sku_variant }}'; name = '{{ $item->item_name }}'; variant = '{{ $item->item_variant }}'; price = '{{ $item->item_price }}'; cogs = '{{ $item->item_cogs }}'; lifetime = '{{ $item->item_lifetime ? \Carbon\Carbon::parse($item->item_lifetime)->format('Y-m-d\TH:i') : '' }}'; status = '{{ $item->item_status }}';" 
                                    class="flex-1 bg-slate-100 active:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-bold text-xs transition text-center shadow-sm">
                                Edit Item
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Belum ada data barang.
                    </div>
                    @endforelse
                </div>

            </div>

        </div>

        {{-- MODAL FORM (CREATE, EDIT & ADD VARIANT) --}}
        <div x-cloak x-show="showModal" x-transition.opacity
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4"
             @keydown.escape.window="showModal = false">
            <div @click.outside="showModal = false" 
                 class="w-full sm:max-w-lg lg:max-w-xl rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl overflow-hidden max-h-[94dvh] sm:max-h-[90vh] flex flex-col border border-slate-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-indigo-50/80 via-white to-slate-50 p-4 sm:p-5 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base" 
                                x-text="isEdit ? 'Edit Item' : (isVariant ? 'Tambah Varian Baru' : 'Tambah Item Baru')"></h3>
                            <p class="text-[10px] sm:text-xs text-slate-400">Atur informasi produk, SKU, harga, dan gambar master item.</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="formUrl" method="POST" enctype="multipart/form-data" 
                      class="p-4 sm:p-6 space-y-4 text-xs overflow-y-auto flex-1 custom-scrollbar">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <template x-if="isVariant">
                        <div>
                            <input type="hidden" name="item_name" :value="name">
                            <input type="hidden" name="item_sku" :value="sku">
                        </div>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                Item SKU <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="item_sku" x-model="sku" :required="!isEdit && !isVariant" :disabled="isEdit || isVariant" placeholder="EX: SKU-001" 
                                   :class="(isEdit || isVariant) ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200 font-mono font-bold' : 'bg-slate-50 border-slate-200 text-slate-800 focus:ring-2 focus:ring-indigo-500'"
                                   class="w-full px-3.5 py-2.5 rounded-xl border focus:outline-none uppercase font-mono text-xs transition">
                            <template x-if="isVariant">
                                <p class="text-[10px] text-slate-400 mt-1">*Induk SKU terkunci.</p>
                            </template>
                            @if($errors->has('item_sku'))
                                <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $errors->first('item_sku') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                SKU Variant <span x-show="isVariant" class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="item_sku_variant" x-model="skuVariant" :required="isVariant" placeholder="EX: SKU-001-RED" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs transition">
                            @if($errors->has('item_sku_variant'))
                                <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $errors->first('item_sku_variant') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                Item Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="item_name" x-model="name" :required="!isVariant" :disabled="isVariant" placeholder="EX: Kopi Susu Aren" 
                                   :class="isVariant ? 'bg-slate-100 text-slate-500 cursor-not-allowed border-slate-200 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-800 focus:ring-2 focus:ring-indigo-500'"
                                   class="w-full px-3.5 py-2.5 rounded-xl border focus:outline-none text-xs transition">
                            <template x-if="isVariant">
                                <p class="text-[10px] text-slate-400 mt-1">*Terkunci dari induk item.</p>
                            </template>
                            @if($errors->has('item_name'))
                                <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $errors->first('item_name') }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                Variant Name <span x-show="isVariant" class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="item_variant" x-model="variant" :required="isVariant" placeholder="EX: Less Sugar / 250ml" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                        <div>
                            <label class="block font-bold text-slate-700 text-[11px] mb-1">Model Image <span class="text-rose-500">*</span></label>
                            <input type="file" name="item_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                            @if($errors->has('item_image'))
                                <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $errors->first('item_image') }}</p>
                            @endif
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 text-[11px] mb-1">Variant Image (Opsional)</label>
                            <input type="file" name="item_variant_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                HPP / COGS (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="item_cogs" x-model="cogs" required min="0" step="100" placeholder="10000" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs transition">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                Harga Jual (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="item_price" x-model="price" required min="0" step="100" placeholder="18000" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                Lifetime / Expired
                            </label>
                            <input type="datetime-local" name="item_lifetime" x-model="lifetime" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs transition">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                                Status <span class="text-rose-500">*</span>
                            </label>
                            <select name="item_status" x-model="status" 
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs font-semibold transition">
                                <option value="available">Available</option>
                                <option value="hold">Hold</option>
                                <option value="discontinue">Discontinue</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-slate-100">
                        <button type="button" @click="showModal = false" class="flex-1 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition text-xs">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/25 transition text-xs active:scale-95" 
                                x-text="isEdit ? 'Simpan Perubahan' : (isVariant ? 'Tambah Varian' : 'Tambah Item')">
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection