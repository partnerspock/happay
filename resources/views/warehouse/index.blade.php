@extends('layouts.app')

@section('title', 'List Warehouse - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory & transfer standard */
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
         showModal: {{ $errors->any() ? 'true' : 'false' }}, 
         isEdit: false, 
         formUrl: '{{ route('warehouse.store') }}', 
         id: '', 
         region: '{{ old('warehouse_region', $selectedRegion ?? '') }}', 
         name: '{{ old('warehouse_name') }}', 
         status: '{{ old('warehouse_status', 'active') }}' 
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                List Warehouse
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Kelola daftar gudang dan lokasi penyimpanan stok per <span class="text-indigo-600 font-semibold">region</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <button type="button" @click="showModal = true; isEdit = false; formUrl = '{{ route('warehouse.store') }}'; region = '{{ $selectedRegion ?? '' }}'; name = ''; status = 'active';"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 active:scale-95 text-white px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-lg shadow-indigo-600/25 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Tambah Warehouse</span>
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

            {{-- 2. FILTER BAR COMPONENT (Fixed 12-col layout) --}}
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 sm:p-5">
                <form action="{{ route('warehouse.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                        
                        {{-- Filter Region Select (Col 5) --}}
                        <div class="sm:col-span-2 lg:col-span-5">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                                Filter Berdasarkan Region
                            </label>
                            <select name="region_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs sm:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="">Semua Region</option>
                                @foreach($regions as $reg)
                                    <option value="{{ $reg->id }}" {{ $selectedRegion == $reg->id ? 'selected' : '' }}>
                                        [{{ $reg->region_alias }}] {{ $reg->region_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Total Info Badge (Col 4) --}}
                        <div class="lg:col-span-4 flex items-center py-2.5 px-3.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-xs text-slate-500 font-medium">Total Terdaftar: <strong class="text-slate-800 font-black">{{ $warehouses->count() }} Warehouse</strong></span>
                        </div>

                        {{-- Filter & Reset Buttons (Col 3) --}}
                        <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-1.5">
                            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white h-[42px] px-3 rounded-xl font-bold text-xs transition flex items-center justify-center shadow-sm">
                                <span>Terapkan Filter</span>
                            </button>
                            @if($selectedRegion)
                                <a href="{{ route('warehouse.index') }}" class="w-[42px] h-[42px] shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center transition active:scale-95" title="Reset Filter">
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
                                <th class="py-3.5 px-4">ID</th>
                                <th class="py-3.5 px-4">Region Code</th>
                                <th class="py-3.5 px-4">Region Name</th>
                                <th class="py-3.5 px-4">Warehouse Name</th>
                                <th class="py-3.5 px-3 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($warehouses as $wh)
                            <tr class="hover:bg-indigo-50/25 transition group">
                                <td class="py-3.5 px-4 text-slate-400 font-mono">#{{ $wh->id }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="bg-slate-100 group-hover:bg-white text-indigo-700 px-2.5 py-1 rounded-lg font-mono font-bold border border-slate-200 text-xs">
                                        {{ $wh->region->region_alias ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    {{ $wh->region->region_name ?? 'N/A' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $wh->warehouse_name }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($wh->warehouse_status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <button @click="showModal = true; isEdit = true; formUrl = '/warehouse/{{ $wh->id }}'; id = '{{ $wh->id }}'; region = '{{ $wh->warehouse_region }}'; name = '{{ $wh->warehouse_name }}'; status = '{{ $wh->warehouse_status }}';" 
                                            class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition text-[11px]">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Belum ada data warehouse.</p>
                                        <p class="text-slate-400 text-xs">Silakan tambahkan warehouse baru atau ubah filter region.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($warehouses as $wh)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-slate-400 text-xs">#{{ $wh->id }}</span>
                            @if($wh->warehouse_status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Inactive</span>
                            @endif
                        </div>

                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ $wh->warehouse_name }}</div>
                            <div class="flex items-center gap-2 mt-1 font-mono">
                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[10px] font-bold">[{{ $wh->region->region_alias ?? 'N/A' }}]</span>
                                <span class="text-slate-500 text-xs font-sans font-medium">{{ $wh->region->region_name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="pt-1">
                            <button @click="showModal = true; isEdit = true; formUrl = '/warehouse/{{ $wh->id }}'; id = '{{ $wh->id }}'; region = '{{ $wh->warehouse_region }}'; name = '{{ $wh->warehouse_name }}'; status = '{{ $wh->warehouse_status }}';" 
                                    class="w-full bg-indigo-50 active:bg-indigo-100 text-indigo-700 py-2.5 rounded-xl font-bold text-xs transition text-center shadow-sm">
                                Edit Warehouse
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Belum ada data warehouse.
                    </div>
                    @endforelse
                </div>

            </div>

        </div>

        {{-- MODAL FORM (CREATE & EDIT) --}}
        <div x-cloak x-show="showModal" x-transition.opacity
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4"
             @keydown.escape.window="showModal = false">
            <div @click.outside="showModal = false" 
                 class="w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl overflow-hidden max-h-[92dvh] sm:max-h-[90vh] flex flex-col border border-slate-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-indigo-50/80 via-white to-slate-50 p-4 sm:p-5 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-md shadow-indigo-600/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base" x-text="isEdit ? 'Edit Warehouse' : 'Tambah Warehouse Baru'"></h3>
                            <p class="text-[10px] sm:text-xs text-slate-400">Atur region dan nama gudang penyimpanan Happay.</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="formUrl" method="POST" class="p-4 sm:p-6 space-y-4 text-xs overflow-y-auto flex-1 custom-scrollbar">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                            Region <span class="text-rose-500">*</span>
                        </label>
                        <select name="warehouse_region" x-model="region" required 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                            <option value="" disabled>-- Pilih Region --</option>
                            @foreach($regions as $reg)
                                <option value="{{ $reg->id }}">[{{ $reg->region_alias }}] {{ $reg->region_name }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('warehouse_region'))
                            <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $errors->first('warehouse_region') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                            Warehouse Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="warehouse_name" x-model="name" required placeholder="Contoh: Gudang Utama JKT" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        @if($errors->has('warehouse_name'))
                            <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $errors->first('warehouse_name') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                            Status <span class="text-rose-500">*</span>
                        </label>
                        <select name="warehouse_status" x-model="status" 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="flex-1 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition text-xs">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/25 transition text-xs active:scale-95" 
                                x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Warehouse'">
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection