<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - {{ $transfer->transfer_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            @page { size: A4 portrait; margin: 15mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 md:p-8" style="-webkit-overflow-scrolling: touch;">

    <div class="no-print max-w-3xl mx-auto mb-6 flex justify-between items-center">
        <button onclick="window.print()" class="text-xs font-bold text-white bg-indigo-600 px-5 py-2.5 rounded-xl shadow-md hover:bg-indigo-700 transition flex items-center space-x-1.5">
            <span>🖨️ Cetak Dokumen</span>
        </button>
    </div>

    <!-- Kertas Dokumen -->
    <div class="max-w-3xl mx-auto bg-white p-6 md:p-10 rounded-2xl border border-slate-200 shadow-sm print:shadow-none print:border-none print:p-0">
        
        <!-- Header Dokumen -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-slate-200 pb-6 mb-6">
            <div class="w-full sm:w-auto">
                <h1 class="text-2xl font-black tracking-wide text-slate-900 uppercase">
                    Stock Transfer
                </h1>
                <div class="text-base font-mono font-bold text-indigo-600 mt-1">
                    {{ $transfer->transfer_id }}
                </div>

                <!-- Info Gudang Pengirim -->
                <div class="mt-4 text-xs space-y-0.5 text-slate-600">
                    <div class="font-bold text-slate-900 text-sm mb-1">GUDANG PENGIRIM (ORIGIN):</div>
                    <div><span class="font-semibold text-slate-700">Warehouse:</span> [{{ $transfer->originWarehouse->region->region_alias ?? 'N/A' }}] {{ $transfer->originWarehouse->warehouse_name }}</div>
                    <div><span class="font-semibold text-slate-700">Pengirim:</span> {{ $transfer->sender->name ?? $transfer->sender->admin_name ?? 'Admin Origin' }}</div>
                    <div><span class="font-semibold text-slate-700">Tanggal Kirim:</span> {{ $transfer->transfer_date_sent ? \Carbon\Carbon::parse($transfer->transfer_date_sent)->format('d/m/Y H:i') : '-' }}</div>
                </div>

                <!-- Info Gudang Penerima -->
                <div class="mt-4 pt-2 text-xs space-y-0.5 text-slate-600">
                    <div class="font-bold text-slate-900 text-sm mb-1">GUDANG PENERIMA (DESTINATION):</div>
                    <div><span class="font-semibold text-slate-700">Warehouse:</span> [{{ $transfer->destinationWarehouse->region->region_alias ?? 'N/A' }}] {{ $transfer->destinationWarehouse->warehouse_name }}</div>
                    <div><span class="font-semibold text-slate-700">Penerima:</span> {{ $transfer->receiver->name ?? $transfer->receiver->admin_name ?? '-' }}</div>
                    <div><span class="font-semibold text-slate-700">Tanggal Terima:</span> {{ $transfer->transfer_date_received ? \Carbon\Carbon::parse($transfer->transfer_date_received)->format('d/m/Y H:i') : '-' }}</div>
                </div>
            </div>

            <!-- Right Logo & QR Code -->
            <div class="w-full sm:w-auto text-left sm:text-right flex flex-row sm:flex-col justify-between sm:justify-start items-center sm:items-end border-t sm:border-t-0 pt-4 sm:pt-0 border-slate-100">
                <div>
                    <div class="text-2xl font-black text-indigo-600 tracking-wider">HAPPAY</div>
                    <div class="text-[10px] text-slate-400 uppercase font-semibold tracking-widest sm:mb-4">POS & Inventory</div>
                </div>

                <div class="flex flex-col items-center sm:items-end">
                    <div class="p-1.5 bg-white border border-slate-200 rounded-lg shadow-sm">
                        @php
                            $searchUrl = route('transfer.index', ['search' => $transfer->transfer_id]);
                            $qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=" . urlencode($searchUrl);
                        @endphp
                        <img src="{{ $qrApi }}" alt="QR Code" class="w-16 h-16 sm:w-20 sm:h-20">
                    </div>
                    <span class="text-[9px] text-slate-400 font-mono mt-1">Scan to Search</span>
                </div>
            </div>
        </div>

        <!-- Items Output Table -->
        <div class="mb-6">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Daftar Barang / Items</h3>
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold uppercase">
                            <th class="py-2.5 px-3 w-12 text-center">No</th>
                            <th class="py-2.5 px-3">SKU</th>
                            <th class="py-2.5 px-3">Nama Barang / Varian</th>
                            <th class="py-2.5 px-3 text-center w-24">Qty Dikirim</th>
                            <th class="py-2.5 px-3 text-center w-24">Qty Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800">
                        @foreach($transfer->items as $index => $item)
                        <tr>
                            <td class="py-2.5 px-3 text-center font-mono">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-semibold">{{ $item->item_sku }}</td>
                            <td class="py-2.5 px-3">
                                <span class="font-semibold">{{ $item->item_name }}</span>
                                @if($item->item_variant)
                                    <span class="text-slate-500 text-[11px]">({{ $item->item_variant }})</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold">{{ $item->qty_sent }}</td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-700">{{ $item->qty_received }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Catatan -->
        <div class="mb-10 p-3.5 border border-slate-200 rounded-xl bg-slate-50 min-h-[60px]">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-0.5">Catatan:</span>
            <p class="text-xs text-slate-700 font-medium">
                {{ $transfer->transfer_notes ? $transfer->transfer_notes : '-' }}
            </p>
        </div>

        <!-- Tanda Tangan Section -->
        <div class="grid grid-cols-2 gap-8 text-center pt-4">
            <div>
                <p class="text-xs font-semibold text-slate-500 mb-16">Pengirim Gudang Asal,</p>
                <div class="w-3/4 mx-auto border-b border-slate-400"></div>
                <p class="text-xs font-bold text-slate-800 mt-1">({{ $transfer->sender->name ?? $transfer->sender->admin_name ?? 'Admin Origin' }})</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 mb-16">Penerima Gudang Tujuan,</p>
                <div class="w-3/4 mx-auto border-b border-slate-400"></div>
                <p class="text-xs font-bold text-slate-800 mt-1">({{ $transfer->receiver->name ?? $transfer->receiver->admin_name ?? 'Admin Destination' }})</p>
            </div>
        </div>

    </div>

</body>
</html>