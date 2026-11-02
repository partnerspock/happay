@extends('layouts.app')

@section('title', 'Master Region - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory, transfer, warehouse, history, audit, stocks, items, report & pos standard */
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
         showModal: {{ $errors->has('region_alias') || $errors->has('region_name') ? 'true' : 'false' }}, 
         isEdit: false, 
         formUrl: '{{ route('region.store') }}', 
         id: '', 
         alias: '{{ old('region_alias') }}', 
         name: '{{ old('region_name') }}', 
         status: '{{ old('region_status', 'active') }}' 
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                Master Region
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Kelola daftar wilayah operasional <span class="text-indigo-600 font-semibold">toko &amp; gudang</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <button type="button" @click="showModal = true; isEdit = false; formUrl = '{{ route('region.store') }}'; alias = ''; name = ''; status = 'active';"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 active:scale-95 text-white px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm shadow-lg shadow-indigo-600/25 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>Tambah Region</span>
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

            {{-- 2. MAIN DATA VIEW --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex justify-between items-center gap-2 bg-slate-50/60">
                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Daftar Wilayah Operasional</span>
                    <span class="text-xs font-mono font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-xl border border-indigo-100">
                        Total: {{ count($regions) }} Region
                    </span>
                </div>
                
                {{-- A. DESKTOP TABLE VIEW --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                            <tr>
                                <th class="py-3.5 px-4">Code</th>
                                <th class="py-3.5 px-4">Region Name</th>
                                <th class="py-3.5 px-3 text-center">Status</th>
                                <th class="py-3.5 px-4">Total Warehouse</th>
                                <th class="py-3.5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($regions as $region)
                            <tr class="hover:bg-indigo-50/25 transition">
                                <td class="py-3.5 px-4 font-mono">
                                    <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-lg font-mono font-bold border border-indigo-100 text-xs">
                                        {{ $region->region_alias }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $region->region_name }}
                                </td>
                                <td class="py-3.5 px-3 text-center">
                                    @if($region->region_status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('warehouse.index', ['region_id' => $region->id]) }}" 
                                       class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 border border-slate-200 hover:border-indigo-200 px-3 py-1.5 rounded-xl font-bold transition text-[11px] shadow-sm">
                                        <span>{{ $region->warehouses_count }} Warehouse</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                        </svg>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <button @click="showModal = true; isEdit = true; formUrl = '/region/{{ $region->id }}'; id = '{{ $region->id }}'; alias = '{{ $region->region_alias }}'; name = '{{ $region->region_name }}'; status = '{{ $region->region_status }}';" 
                                            class="inline-flex items-center gap-1 font-semibold text-slate-700 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition text-[11px]">
                                        <span>Edit</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Belum ada data region.</p>
                                        <p class="text-slate-400 text-xs">Silakan tambahkan region baru untuk mengelompokkan warehouse.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($regions as $region)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-lg font-mono font-bold text-xs border border-indigo-100">{{ $region->region_alias }}</span>
                            @if($region->region_status === 'active')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Inactive</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="font-bold text-slate-900 text-sm">{{ $region->region_name }}</div>
                            <a href="{{ route('warehouse.index', ['region_id' => $region->id]) }}" 
                               class="text-indigo-600 font-bold text-xs inline-flex items-center gap-1 bg-indigo-50 px-2.5 py-1 rounded-xl border border-indigo-100">
                                {{ $region->warehouses_count }} Warehouse &rarr;
                            </a>
                        </div>

                        <div class="pt-1">
                            <button @click="showModal = true; isEdit = true; formUrl = '/region/{{ $region->id }}'; id = '{{ $region->id }}'; alias = '{{ $region->region_alias }}'; name = '{{ $region->region_name }}'; status = '{{ $region->region_status }}';" 
                                    class="w-full bg-slate-100 active:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-bold text-xs transition text-center shadow-sm">
                                Edit Region
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Belum ada data region.
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
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base" x-text="isEdit ? 'Edit Region' : 'Tambah Region Baru'"></h3>
                            <p class="text-[10px] sm:text-xs text-slate-400">Atur kode alias dan nama wilayah operasional.</p>
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
                            Alias Code (Wajib 3 Karakter) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="region_alias" x-model="alias" maxlength="3" :required="!isEdit" :disabled="isEdit"
                                   @input="alias = alias.toUpperCase().replace(/[^A-Z0-9]/g, '')" placeholder="EX: JKT" 
                                   :class="isEdit ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200' : ('{{ $errors->has('region_alias') }}' || (alias.length > 0 && alias.length < 3) ? 'border-rose-400 focus:ring-rose-500 bg-slate-50 text-slate-800' : 'border-slate-200 focus:ring-indigo-500 bg-slate-50 text-slate-800')"
                                   class="w-full px-3.5 py-2.5 rounded-xl border focus:outline-none uppercase font-mono text-xs transition pr-12">
                            
                            <div x-show="!isEdit" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono font-bold"
                                 :class="alias.length === 3 ? 'text-emerald-500' : 'text-slate-400'">
                                <span x-text="alias.length"></span>/3
                            </div>
                        </div>

                        @if($errors->has('region_alias'))
                            <p class="text-[10px] text-rose-500 font-bold mt-1">
                                {{ $errors->first('region_alias') }}
                            </p>
                        @endif

                        <template x-if="!isEdit && alias.length > 0 && alias.length < 3">
                            <p class="text-[10px] text-rose-500 font-bold mt-1">
                                Alias harus persis 3 karakter (kurang <span x-text="3 - alias.length"></span> karakter lagi).
                            </p>
                        </template>

                        <template x-if="isEdit">
                            <p class="text-[10px] text-slate-400 mt-1">*Alias region tidak dapat diubah setelah dibuat.</p>
                        </template>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                            Nama Region <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="region_name" x-model="name" required placeholder="EX: Jakarta Central" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-xs transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 uppercase tracking-wider text-[10px] mb-1.5">
                            Status <span class="text-rose-500">*</span>
                        </label>
                        <select name="region_status" x-model="status" 
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold text-xs transition">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-2 border-t border-slate-100">
                        <button type="button" @click="showModal = false" class="flex-1 py-3 rounded-xl font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition text-xs">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="!isEdit && alias.length !== 3"
                                :class="(!isEdit && alias.length !== 3) ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/25 active:scale-95'"
                                class="flex-1 py-3 rounded-xl font-bold transition text-xs" 
                                x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Region'">
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection