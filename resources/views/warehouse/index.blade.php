@extends('layouts.app')

@section('title', 'List Warehouse - Happay')

@section('content')
<!-- Wrapper Utama dengan skala 80% dan tinggi penuh -->
<div class="w-full h-full overflow-hidden bg-slate-100" style="zoom: 0.8;">
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;" 
     x-data="{ 
         showModal: {{ $errors->any() ? 'true' : 'false' }}, 
         isEdit: false, 
         formUrl: '{{ route('warehouse.store') }}', 
         id: '', 
         region: '{{ old('warehouse_region', $selectedRegion ?? '') }}', 
         name: '{{ old('warehouse_name') }}', 
         status: '{{ old('warehouse_status', 'active') }}' 
     }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">List Warehouse</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola daftar gudang dan lokasi penyimpanan stok per region.</p>
        </div>
        <button @click="showModal = true; isEdit = false; formUrl = '{{ route('warehouse.store') }}'; region = '{{ $selectedRegion ?? '' }}'; name = ''; status = 'active';" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center space-x-2 shrink-0">
            <span>+ Tambah Warehouse</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
            <span>Filter Region:</span>
            <form action="{{ route('warehouse.index') }}" method="GET" class="flex items-center space-x-2">
                <select name="region_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none text-xs font-medium">
                    <option value="">Semua Region</option>
                    @foreach($regions as $reg)
                        <option value="{{ $reg->id }}" {{ $selectedRegion == $reg->id ? 'selected' : '' }}>
                            [{{ $reg->region_alias }}] {{ $reg->region_name }}
                        </option>
                    @endforeach
                </select>
                @if($selectedRegion)
                    <a href="{{ route('warehouse.index') }}" class="text-rose-500 hover:text-rose-700 text-xs font-bold underline">Reset Filter</a>
                @endif
            </form>
        </div>

        <div class="text-xs text-slate-400 font-medium">
            Total: <span class="font-bold text-slate-700">{{ $warehouses->count() }} Warehouse</span>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        {{-- ============ TABEL (iPad & desktop, >= 768px) ============ --}}
        <div class="hidden md:block overflow-x-auto max-h-[65vh]">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 bg-slate-50/80 border-b border-slate-200/80 text-xs text-slate-500 uppercase tracking-wider font-semibold z-10">
                    <tr>
                        <th class="py-3.5 px-6">ID</th>
                        <th class="py-3.5 px-6">Region Code</th>
                        <th class="py-3.5 px-6">Region Name</th>
                        <th class="py-3.5 px-6">Warehouse Name</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($warehouses as $wh)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6 text-slate-400 font-mono">#{{ $wh->id }}</td>
                        <td class="py-4 px-6">
                            <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-lg font-mono font-bold">
                                {{ $wh->region->region_alias ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="bg-violet-50 text-violet-700 px-2.5 py-1 rounded-lg font-mono font-bold">
                                {{ $wh->region->region_name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-900">{{ $wh->warehouse_name }}</td>
                        <td class="py-4 px-6">
                            @if($wh->warehouse_status === 'active')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold">Active</span>
                            @else
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold">Inactive</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right">
                            <button @click="showModal = true; isEdit = true; formUrl = '/warehouse/{{ $wh->id }}'; id = '{{ $wh->id }}'; region = '{{ $wh->warehouse_region }}'; name = '{{ $wh->warehouse_name }}'; status = '{{ $wh->warehouse_status }}';" 
                                    class="font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 text-sm">Belum ada data warehouse.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100 text-xs">
            @forelse($warehouses as $wh)
            <div class="p-3.5 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-slate-400">#{{ $wh->id }}</span>
                    @if($wh->warehouse_status === 'active')
                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px]">Active</span>
                    @else
                        <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full font-semibold text-[10px]">Inactive</span>
                    @endif
                </div>

                <div>
                    <div class="font-bold text-slate-900 text-sm">{{ $wh->warehouse_name }}</div>
                    <div class="flex items-center gap-2 mt-1.5 font-mono">
                        <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[10px] font-bold">{{ $wh->region->region_alias ?? 'N/A' }}</span>
                        <span class="text-slate-500 text-[11px]">{{ $wh->region->region_name ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="pt-1">
                    <button @click="showModal = true; isEdit = true; formUrl = '/warehouse/{{ $wh->id }}'; id = '{{ $wh->id }}'; region = '{{ $wh->warehouse_region }}'; name = '{{ $wh->warehouse_name }}'; status = '{{ $wh->warehouse_status }}';" 
                            class="w-full bg-indigo-50 text-indigo-600 active:bg-indigo-100 py-2.5 rounded-xl font-semibold transition text-center">
                        Edit Warehouse
                    </button>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">Belum ada data warehouse.</div>
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
                <h3 class="font-bold text-slate-900 text-sm md:text-base" x-text="isEdit ? 'Edit Warehouse' : 'Tambah Warehouse Baru'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none font-bold px-2">&times;</button>
            </div>

            <form :action="formUrl" method="POST" class="p-4 md:p-6 space-y-4 text-xs overflow-y-auto">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Select Region -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Region</label>
                    <select name="warehouse_region" x-model="region" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                        <option value="" disabled>-- Pilih Region --</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg->id }}">[{{ $reg->region_alias }}] {{ $reg->region_name }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('warehouse_region'))
                        <p class="text-[11px] text-rose-500 font-medium mt-1">* {{ $errors->first('warehouse_region') }}</p>
                    @endif
                </div>

                <!-- Input Warehouse Name -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Warehouse Name</label>
                    <input type="text" 
                           name="warehouse_name" 
                           x-model="name" 
                           required 
                           placeholder="EX: Gudang Utama JKT" 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                    @if($errors->has('warehouse_name'))
                        <p class="text-[11px] text-rose-500 font-medium mt-1">* {{ $errors->first('warehouse_name') }}</p>
                    @endif
                </div>

                <!-- Select Status -->
                <div>
                    <label class="block font-semibold text-slate-600 uppercase tracking-wider mb-2">Status</label>
                    <select name="warehouse_status" x-model="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base md:text-xs font-medium">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="pt-2 flex gap-2">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-3 md:py-2.5 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition" 
                            x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Warehouse'">
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>
@endsection