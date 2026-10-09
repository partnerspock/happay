@extends('layouts.app')

@section('title', 'POS Transactions - Happay')

@section('content')
@php
    // Style & label status dipakai bersama oleh tabel (desktop/iPad) dan kartu (HP)
    $statusClass = [
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'draft'     => 'bg-slate-100 text-slate-700 border-slate-200',
        'void'      => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
    $statusLabel = ['completed' => 'Completed', 'draft' => 'Draft', 'void' => 'Void'];
@endphp

{{-- h-full + overflow-y-auto: halaman bisa di-scroll sendiri (layout induk memakai overflow-hidden) --}}
<div class="h-full overflow-y-auto p-4 md:p-6 lg:p-8" style="-webkit-overflow-scrolling: touch;" x-data="posIndexData()">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4 md:mb-6">
        <div class="min-w-0">
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">POS Transactions</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">Kelola transaksi penjualan, kasir, dan riwayat pernominalan.</p>
        </div>
        <a href="{{ route('pos.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center shrink-0">
            <span>+ Buka Kasir (POS)</span>
        </a>
    </div>

    <!-- Filter Bar: 1 kolom (HP) -> 2 kolom (iPad portrait) -> 4 kolom (desktop) -->
    <form action="{{ route('pos.index') }}" method="GET" class="mb-4 md:mb-6 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 items-end">
        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Cari Transaksi</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="No Invoice / Nama Customer..."
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Warehouse</label>
            <select name="warehouse_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Warehouse</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ $selectedWarehouse == $wh->id ? 'selected' : '' }}>
                        [{{ optional($wh->region)->region_alias ?? 'N/A' }}] {{ $wh->warehouse_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="min-w-0">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Status</label>
            <select name="transaction_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-base md:text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="completed" {{ $selectedStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="draft" {{ $selectedStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="void" {{ $selectedStatus === 'void' ? 'selected' : '' }}>Void</option>
                <option value="cancel" {{ $selectedStatus === 'cancel' ? 'selected' : '' }}>Cancel</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 md:py-2.5 rounded-xl font-bold text-xs shadow-md shadow-indigo-600/20 transition">
                Filter Data
            </button>
            <a href="{{ route('pos.index') }}" class="px-4 md:px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl flex items-center justify-center font-bold text-xs transition" title="Reset Filter">
                🔄
            </a>
        </div>
    </form>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-3 md:p-4 border-b border-slate-100 flex justify-between items-center gap-2 bg-slate-50/50">
            <span class="text-xs font-bold text-slate-600">Daftar Transaksi POS</span>
            <span class="text-xs font-mono font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg whitespace-nowrap">
                Total: {{ $transactions->total() }} Data
            </span>
        </div>

        {{-- ============ TABEL (iPad & desktop, >= 768px) ============ --}}
        <div class="hidden md:block overflow-auto max-h-[65vh]">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 bg-slate-50 border-b border-slate-200/80 text-xs text-slate-500 uppercase tracking-wider font-semibold z-10">
                    <tr>
                        <th class="py-3 px-3 lg:px-6">Invoice UUID</th>
                        <th class="py-3 px-3 lg:px-6">Warehouse & Kasir</th>
                        <th class="py-3 px-3 lg:px-6">Customer</th>
                        <th class="py-3 px-3 lg:px-6 text-right">Total Price</th>
                        <th class="py-3 px-3 lg:px-6 text-right">Margin</th>
                        <th class="py-3 px-3 lg:px-6 text-center">Status</th>
                        <th class="py-3 px-3 lg:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium text-xs">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-3 lg:px-6 font-mono font-bold text-indigo-600">
                            <span class="break-all">{{ $trx->uuid }}</span>
                            <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $trx->created_at ? $trx->created_at->format('d M Y, H:i') : '-' }}</div>
                        </td>
                        <td class="py-3.5 px-3 lg:px-6">
                            <div class="font-bold text-slate-900">[{{ optional(optional($trx->warehouse)->region)->region_alias ?? 'N/A' }}] {{ optional($trx->warehouse)->warehouse_name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400">Kasir: {{ optional($trx->cashier)->admin_name ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-3 lg:px-6">
                            <div class="font-semibold text-slate-800">{{ $trx->transaction_info_name ?? 'Walk-in' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $trx->transaction_info_number ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-3 lg:px-6 text-right font-mono font-bold text-slate-900 whitespace-nowrap">
                            Rp {{ number_format(($trx->transaction_pricetotal + $trx->transaction_tax), 0, ',', '.') }}
                            <div class="text-[9px] text-slate-400 font-normal">Tax: Rp {{ number_format($trx->transaction_tax, 0, ',', '.') }}</div>
                        </td>
                        <td class="py-3.5 px-3 lg:px-6 text-right font-mono font-semibold text-emerald-600 whitespace-nowrap">
                            Rp {{ number_format($trx->transaction_margin, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-3 lg:px-6 text-center">
                            <span class="border px-2.5 py-0.5 rounded-full font-semibold {{ $statusClass[$trx->transaction_status] ?? 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                {{ $statusLabel[$trx->transaction_status] ?? 'Cancel' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-3 lg:px-6 text-right whitespace-nowrap">
                            <a href="{{ route('pos.print', $trx->id) }}" target="_blank" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg font-semibold transition inline-block">
                                🖨️ Struk
                            </a>

                            @if($trx->transaction_status === 'draft')
                                <form action="{{ route('pos.update-status', $trx->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="transaction_status" value="completed">
                                    <button type="submit" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg font-semibold transition">Complete</button>
                                </form>
                            @elseif($trx->transaction_status === 'completed')
                                <button type="button" @click="openVoidModal('{{ $trx->id }}', '{{ $trx->uuid }}')" class="bg-rose-50 text-rose-600 hover:bg-rose-100 px-2.5 py-1.5 rounded-lg font-semibold transition">
                                    Void
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-400 text-sm">Belum ada riwayat transaksi POS.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ============ KARTU (HP, < 768px) ============ --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($transactions as $trx)
            <div class="p-3.5 space-y-2.5 text-xs">

                <!-- Baris 1: Invoice + Status -->
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-mono font-bold text-indigo-600 break-all">{{ $trx->uuid }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $trx->created_at ? $trx->created_at->format('d M Y, H:i') : '-' }}</div>
                    </div>
                    <span class="border px-2.5 py-0.5 rounded-full font-semibold shrink-0 {{ $statusClass[$trx->transaction_status] ?? 'bg-amber-50 text-amber-700 border-amber-200' }}">
                        {{ $statusLabel[$trx->transaction_status] ?? 'Cancel' }}
                    </span>
                </div>

                <!-- Baris 2: Warehouse & Customer -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Warehouse</div>
                        <div class="font-bold text-slate-900 truncate">[{{ optional(optional($trx->warehouse)->region)->region_alias ?? 'N/A' }}] {{ optional($trx->warehouse)->warehouse_name ?? '-' }}</div>
                        <div class="text-[10px] text-slate-400 truncate">Kasir: {{ optional($trx->cashier)->admin_name ?? '-' }}</div>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Customer</div>
                        <div class="font-semibold text-slate-800 truncate">{{ $trx->transaction_info_name ?? 'Walk-in' }}</div>
                        <div class="text-[10px] text-slate-400 font-mono truncate">{{ $trx->transaction_info_number ?? '-' }}</div>
                    </div>
                </div>

                <!-- Baris 3: Total & Margin -->
                <div class="grid grid-cols-2 gap-3 bg-slate-50 rounded-xl p-2.5">
                    <div>
                        <div class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Total</div>
                        <div class="font-mono font-bold text-slate-900">Rp {{ number_format(($trx->transaction_pricetotal + $trx->transaction_tax), 0, ',', '.') }}</div>
                        <div class="text-[9px] text-slate-400">Tax: Rp {{ number_format($trx->transaction_tax, 0, ',', '.') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Margin</div>
                        <div class="font-mono font-semibold text-emerald-600">Rp {{ number_format($trx->transaction_margin, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Aksi (tombol besar supaya mudah ditekan) -->
                <div class="flex gap-2">
                    <a href="{{ route('pos.print', $trx->id) }}" target="_blank" class="flex-1 text-center bg-indigo-50 text-indigo-600 active:bg-indigo-100 py-2.5 rounded-lg font-semibold transition">
                        🖨️ Struk
                    </a>

                    @if($trx->transaction_status === 'draft')
                        <form action="{{ route('pos.update-status', $trx->id) }}" method="POST" class="flex-1">
                            @csrf @method('PATCH')
                            <input type="hidden" name="transaction_status" value="completed">
                            <button type="submit" class="w-full bg-emerald-50 text-emerald-600 active:bg-emerald-100 py-2.5 rounded-lg font-semibold transition">Complete</button>
                        </form>
                    @elseif($trx->transaction_status === 'completed')
                        <button type="button" @click="openVoidModal('{{ $trx->id }}', '{{ $trx->uuid }}')" class="flex-1 bg-rose-50 text-rose-600 active:bg-rose-100 py-2.5 rounded-lg font-semibold transition">
                            Void
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-14 text-center text-slate-400 text-sm">Belum ada riwayat transaksi POS.</div>
            @endforelse
        </div>

        @if($transactions->hasPages())
            <div class="p-3 md:p-4 border-t border-slate-100 bg-slate-50 overflow-x-auto">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Spacer bawah supaya item terakhir tidak mepet tepi layar HP -->
    <div class="h-6 md:h-0"></div>

    <!-- Modal Verifikasi Password SPV / Manager untuk Void -->
    <div x-show="showVoidModal" x-transition class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[60] flex items-center justify-center p-3 md:p-4" style="display: none;">
        <div @click.away="showVoidModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl w-full max-w-md overflow-hidden flex flex-col" style="max-height: 92vh; max-height: 92dvh;">
            <div class="p-4 md:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <h3 class="font-bold text-slate-900 text-sm">Otorisasi Void Transaksi</h3>
                <button type="button" @click="showVoidModal = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none font-bold px-2">&times;</button>
            </div>

            <form :action="'/pos/' + selectedTrxId + '/status'" method="POST" class="p-4 md:p-6 space-y-4 text-xs overflow-y-auto">
                @csrf @method('PATCH')
                <input type="hidden" name="transaction_status" value="void">

                <!-- Notifikasi Error jika Nama Admin / Password Salah -->
                @if($errors->has('spv_password'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 p-3 rounded-xl font-semibold">
                        ❌ {{ $errors->first('spv_password') }}
                    </div>
                @endif

                <div class="bg-amber-50 border border-amber-200 text-amber-800 p-3 rounded-xl">
                    ⚠️ Anda akan melakukan <b>Void</b> pada transaksi ini. Stok produk akan dikembalikan ke gudang secara otomatis. Tindakan ini memerlukan otorisasi Supervisor / Manager.
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Nama Admin SPV / Manager</label>
                    <input type="text" name="spv_username" value="{{ old('spv_username') }}" required placeholder="Masukkan nama admin SPV/Mgr..." autocomplete="off" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-medium text-base md:text-xs focus:outline-none focus:ring-2 focus:ring-rose-400">
                </div>

                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Password SPV / Manager</label>
                    <input type="password" name="spv_password" required placeholder="••••••••" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-medium text-base md:text-xs focus:outline-none focus:ring-2 focus:ring-rose-400">
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showVoidModal = false" class="flex-1 sm:flex-none px-4 py-3 md:py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 sm:flex-none px-4 py-3 md:py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold shadow-md shadow-rose-600/20 transition">
                        Konfirmasi Void
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function posIndexData() {
    return {
        // Jika ada error dari Laravel, modal otomatis terbuka kembali
        showVoidModal: {{ $errors->has('spv_password') ? 'true' : 'false' }},
        // Mengambil ID transaksi terakhir jika terjadi error validasi password
        selectedTrxId: '{{ session("failed_trx_id", $transactions->first()->id ?? 1) }}',
        selectedTrxUuid: '',

        openVoidModal(id, uuid) {
            this.selectedTrxId = id;
            this.selectedTrxUuid = uuid;
            this.showVoidModal = true;
        }
    }
}
</script>
@endsection