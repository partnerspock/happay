<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $transaction->uuid }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            @page { size: 80mm auto; margin: 2mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-mono text-xs p-4 md:p-8" style="-webkit-overflow-scrolling: touch;">

    <!-- Tombol Navigasi & Cetak (Hilang otomatis saat diprint) -->
    <div class="no-print max-w-xs mx-auto mb-4 flex justify-between items-center">
        <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-xl text-[11px] font-bold shadow-md transition flex items-center space-x-1">
            <span>🖨️ Cetak Struk</span>
        </button>
    </div>

    <!-- Kertas Struk Thermal -->
    <div class="max-w-xs mx-auto bg-white p-5 md:p-6 border border-slate-200 rounded-xl shadow-sm print:shadow-none print:border-none print:p-0">
        
        <!-- Header Struk -->
        <div class="text-center mb-4">
            <h2 class="text-base font-black tracking-widest text-slate-900">HAPPAY STORE</h2>
            <p class="text-[10px] text-slate-500">[{{ $transaction->warehouse->region->region_alias ?? 'N/A' }}] {{ $transaction->warehouse->warehouse_name }}</p>
            <p class="text-[10px] text-slate-400 mt-1">{{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i') : '-' }}</p>
        </div>

        <!-- Detail Transaksi -->
        <div class="border-b border-dashed border-slate-300 pb-2 mb-3 text-[11px] space-y-0.5">
            <div><span class="font-bold">No. Inv:</span> <span class="break-all">{{ $transaction->uuid }}</span></div>
            <div><span class="font-bold">Kasir:</span> {{ $transaction->cashier->name ?? $transaction->cashier->admin_name ?? '-' }}</div>
            <div><span class="font-bold">Customer:</span> {{ $transaction->transaction_info_name ?? 'Umum' }}</div>
        </div>

        <!-- Daftar Item -->
        <div class="space-y-2 mb-4 text-[11px]">
            @foreach($transaction->items as $item)
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <div class="font-bold truncate">{{ $item->item->item_name ?? '-' }}</div>
                    <div class="text-[10px] text-slate-500">{{ $item->transaction_item_qty }} x @ Rp {{ number_format($item->transaction_item_priceperitem, 0, ',', '.') }}</div>
                </div>
                <div class="font-bold shrink-0">Rp {{ number_format($item->transaction_item_pricesubtotal, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>

        <!-- Rangkuman Harga -->
        <div class="border-t border-dashed border-slate-300 pt-2 space-y-1 mb-4 text-[11px]">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>Rp {{ number_format($transaction->transaction_pricetotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pajak (11%)</span>
                <span>Rp {{ number_format($transaction->transaction_tax, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-bold text-xs pt-1.5 border-t border-slate-200">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaction->transaction_pricetotal + $transaction->transaction_tax, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer Struk -->
        <div class="text-center text-[10px] text-slate-400 pt-2 border-t border-slate-100">
            <p>Terima Kasih Atas Kunjungan Anda!</p>
            <p class="font-mono mt-0.5">Happay POS Ecosystem</p>
        </div>
    </div>

</body>
</html>