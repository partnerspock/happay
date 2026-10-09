@extends('layouts.app')

@section('title', 'Master Region - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;" 
     x-data="{ 
         showModal: {{ $errors->has('region_alias') || $errors->has('region_name') ? 'true' : 'false' }}, 
         isEdit: false, 
         formUrl: '{{ route('region.store') }}', 
         id: '', 
         alias: '{{ old('region_alias') }}', 
         name: '{{ old('region_name') }}', 
         status: '{{ old('region_status', 'active') }}' 
     }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">Master Region</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola daftar wilayah operasional toko & gudang.</p>
        </div>
        <button @click="showModal = true; isEdit = false; formUrl = '{{ route('region.store') }}'; alias = ''; name = ''; status = 'active';" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center space-x-2 shrink-0">
            <span>+ Tambah Region</span>
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        {{-- ============ TABEL (iPad & desktop, >= 768px) ============ --}}
        <div class="hidden md:block overflow-x-auto max-h-[65vh]">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 bg-slate-50/80 border-b border-slate-200/80 text-xs text-slate-500 uppercase tracking-wider font-semibold z-10">
                    <tr>
                        <th class="py-3.5 px-6">Code</th>
                        <th class="py-3.5 px-6">Region Name</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6">Total Warehouse</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($regions as $region)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6">
                            <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-lg font-mono font-bold">{{ $region->region_alias }}</span>
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-900">{{ $region->region_name }}</td>
                        <td class="py-4 px-6">
                            @if($region->region_status === 'active')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold">Active</span>
                            @else
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold">Inactive</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <a href="{{ route('warehouse.index', ['region_id' => $region->id]) }}" 
                               class="inline-flex items-center space-x-1 bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 border border-transparent hover:border-indigo-200 px-2.5 py-1 rounded-full font-semibold transition cursor-pointer"
                               title="Lihat warehouse di region {{ $region->region_name }}">
                                <span>{{ $region->warehouses_count }} Warehouse</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <button @click="showModal = true; isEdit = true; formUrl = '/region/{{ $region->id }}'; id = '{{ $region->id }}'; alias = '{{ $region->region_alias }}'; name = '{{ $region->region_name }}'; status = '{{ $region->region_status }}';" 
                                    class="font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-sm">Belum ada data region.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100 text-xs">
            @forelse($regions as $region)
            <div class="p-3.5 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-mono font-bold text-xs">{{ $region->region_alias }}</span>
                    @if($region->region_status === 'active')
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px]">Active</span>
                    @else
                        <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px]">Inactive</span>
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <div class="font-bold text-slate-900 text-sm">{{ $region->region_name }}</div>
                    <a href="{{ route('warehouse.index', ['region_id' => $region->id]) }}" 
                       class="text-indigo-600 font-semibold underline text-[11px]">
                        {{ $region->warehouses_count }} Warehouse &rarr;
                    </a>
                </div>

                <div class="pt-1">
                    <button @click="showModal = true; isEdit = true; formUrl = '/region/{{ $region->id }}'; id = '{{ $region->id }}'; alias = '{{ $region->region_alias }}'; name = '{{ $region->region_name }}'; status = '{{ $region->region_status }}';" 
                            class="w-full bg-indigo-50 text-indigo-600 active:bg-indigo-100 py-2.5 rounded-xl font-semibold transition text-center">
                        Edit Region
                    </button>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">Belum ada data region.</div>
            @endforelse
        </div>

    </div>

    <!-- Modal Form (Create & Edit) -->
    <div x-show="showModal" 
         x-transition 
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" 
         style="display: none;">
        
        <div @click.away="showModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-md overflow-hidden flex flex-col" style="max-height: 92vh; max-height: 92dvh;">
            
            <!-- Modal Header -->
            <div class="p-4 md:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h3 class="font-bold text-slate-900 text-sm md:text-base" x-text="isEdit ? 'Edit Region' : 'Tambah Region Baru'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none font-bold px-2">&times;</button>
            </div>

            <form :action="formUrl" method="POST" class="p-4 md:p-6 space-y-4 text-xs overflow-y-auto flex-1">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Input Alias Code -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">
                        Alias Code (Wajib 3 Karakter)
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="region_alias" 
                               x-model="alias" 
                               maxlength="3" 
                               :required="!isEdit"
                               :disabled="isEdit"
                               @input="alias = alias.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                               placeholder="EX: JKT" 
                               :class="isEdit 
                                   ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200' 
                                   : ('{{ $errors->has('region_alias') }}' || (alias.length > 0 && alias.length < 3) 
                                       ? 'border-rose-400 focus:ring-rose-500 bg-slate-50 text-slate-800' 
                                       : 'border-slate-200 focus:ring-indigo-500 bg-slate-50 text-slate-800')"
                               class="w-full px-4 py-2.5 rounded-xl border focus:outline-none uppercase font-mono text-base md:text-xs transition pr-12">
                        
                        <div x-show="!isEdit" class="absolute right-3 top-3 text-xs font-mono font-bold"
                             :class="alias.length === 3 ? 'text-emerald-500' : 'text-slate-400'">
                            <span x-text="alias.length"></span>/3
                        </div>
                    </div>

                    @if($errors->has('region_alias'))
                        <p class="text-[11px] text-rose-500 font-medium mt-1">
                            * {{ $errors->first('region_alias') }}
                        </p>
                    @endif

                    <template x-if="!isEdit && alias.length > 0 && alias.length < 3">
                        <p class="text-[11px] text-rose-500 font-medium mt-1">
                            * Alias harus persis 3 karakter (kurang <span x-text="3 - alias.length"></span> karakter lagi).
                        </p>
                    </template>

                    <template x-if="isEdit">
                        <p class="text-[10px] text-slate-400 mt-1">*Alias region tidak dapat diubah setelah dibuat.</p>
                    </template>
                </div>

                <!-- Input Nama Region -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Nama Region</label>
                    <input type="text" 
                           name="region_name" 
                           x-model="name" 
                           required 
                           placeholder="EX: Jakarta Central" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                </div>

                <!-- Select Status -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Status</label>
                    <select name="region_status" x-model="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="pt-2 flex gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="!isEdit && alias.length !== 3"
                            :class="(!isEdit && alias.length !== 3) 
                                ? 'bg-slate-300 text-slate-500 cursor-not-allowed' 
                                : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20'"
                            class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-bold transition" 
                            x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Region'">
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
@endsection