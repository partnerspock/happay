@extends('layouts.app')

@section('title', 'Detail Riwayat Stok SKU - Happay')

@push('styles')
<style>
    /* Desktop optimization matching inventory, transfer & warehouse standard */
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
<div class="w-full h-full overflow-hidden bg-slate-100 app-content-zoom">
    <div class="h-full overflow-y-auto custom-scrollbar" style="-webkit-overflow-scrolling: touch;">

        {{-- ===== PAGE SHELL ===== --}}
        <div class="p-3.5 sm:p-5 lg:p-7 max-w-7xl mx-auto space-y-4 sm:space-y-6 pb-20 sm:pb-12">

            {{-- 1. TOP HEADER --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="min-w-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight">
                                Detail Riwayat Stok SKU
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                Audit log pergerakan stok untuk <span class="text-indigo-600 font-semibold">item dan gudang tertentu</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1 sm:pt-0">
                    <a href="{{ route('warehouse.stock') }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 px-4 py-2.5 sm:py-3 rounded-xl font-bold text-xs sm:text-sm border border-slate-200 transition-all shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        <span>Kembali ke List Stocks</span>
                    </a>
                </div>
            </div>

            {{-- 2. INFO CARDS (Warehouse & SKU) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Warehouse Card --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 shrink-0 font-bold">
                        🏢
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Informasi Warehouse</span>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-900">
                            [{{ $warehouse->region->region_alias ?? 'N/A' }}] {{ $warehouse->warehouse_name }}
                        </div>
                        <div class="text-xs text-slate-500 font-medium mt-0.5 truncate">{{ $warehouse->warehouse_address ?? '-' }}</div>
                    </div>
                </div>

                {{-- Item SKU Card --}}
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 font-bold">
                        📦
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Informasi Item SKU</span>
                        <div class="text-xs sm:text-sm font-extrabold text-slate-900 truncate">{{ $item->item_name }}</div>
                        <div class="inline-flex items-center gap-1.5 mt-1 font-mono text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                            <span>SKU: {{ $item->item_sku }}</span>
                            @if($item->item_variant)
                                <span class="text-indigo-400">({{ $item->item_variant }})</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. MAIN DATA VIEW --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                
                {{-- A. DESKTOP TABLE VIEW --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-slate-50/90 border-b border-slate-200/80 text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                            <tr>
                                <th class="py-3.5 px-4 text-center">Before</th>
                                <th class="py-3.5 px-4 text-center">Change</th>
                                <th class="py-3.5 px-4 text-center">Now</th>
                                <th class="py-3.5 px-4">Type &amp; Description</th>
                                <th class="py-3.5 px-4 text-right">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                            @forelse($histories as $hist)
                            <tr class="hover:bg-indigo-50/25 transition">
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-600">
                                    {{ $hist->history_before }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold {{ $hist->history_change__type === 'increased' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $hist->history_change__type === 'increased' ? '+' : '-' }}{{ $hist->history_change }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-extrabold text-slate-900">
                                    {{ $hist->history_now }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($hist->history_change__type === 'increased')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200 mb-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                            Increased
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-rose-50 text-rose-700 border border-rose-200 mb-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                            Decreased
                                        </span>
                                    @endif
                                    <div class="text-slate-600 font-medium text-[11px]">{{ $hist->history_change_description ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-slate-400 text-[11px]">
                                    {{ optional($hist->created_at)->format('d M Y, H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="inline-flex flex-col items-center gap-2 text-slate-300">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 border border-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                                            </svg>
                                        </div>
                                        <p class="text-slate-600 font-bold text-sm">Belum ada riwayat pergerakan stok.</p>
                                        <p class="text-slate-400 text-xs">Belum ada audit log tercatat untuk SKU ini di warehouse tersebut.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- B. MOBILE VIEW --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse($histories as $hist)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-slate-400 text-[10px]">
                                {{ optional($hist->created_at)->format('d M Y, H:i') }}
                            </span>
                            @if($hist->history_change__type === 'increased')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Increased</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Decreased</span>
                            @endif
                        </div>

                        {{-- Perubahan Qty Card --}}
                        <div class="grid grid-cols-3 gap-2 text-center bg-slate-50 p-2.5 rounded-xl border border-slate-100 font-mono text-xs">
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Before</span>
                                <span class="font-bold text-slate-700">{{ $hist->history_before }}</span>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Change</span>
                                <span class="font-black {{ $hist->history_change__type === 'increased' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $hist->history_change__type === 'increased' ? '+' : '-' }}{{ $hist->history_change }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Now</span>
                                <span class="font-black text-slate-900">{{ $hist->history_now }}</span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="bg-slate-50/70 rounded-xl p-3 border border-slate-100">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block mb-0.5">Description</span>
                            <p class="text-slate-700 text-xs font-medium">{{ $hist->history_change_description ?? '-' }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-sm font-medium">
                        Belum ada riwayat pergerakan stok untuk SKU ini di warehouse tersebut.
                    </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if(method_exists($histories, 'hasPages') && $histories->hasPages())
                <div class="p-3.5 sm:p-4 border-t border-slate-100 bg-slate-50/60">
                    {{ $histories->links() }}
                </div>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection