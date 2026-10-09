<?php

namespace App\Http\Controllers;

use App\Models\StockTransferMaster;
use App\Models\StockTransferItem;
use App\Models\WarehouseMaster;
use App\Models\ItemMaster;
use App\Models\StockMaster;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $originWarehouseId = $request->input('warehouse_origin_id', '');
        $destinationWarehouseId = $request->input('warehouse_destination_id', '');
        $selectedStatus = $request->input('transfer_status', '');

        $admin = Auth::user();
        $adminRegionId = $admin->region_id ?? null;

        $query = StockTransferMaster::with([
            'originWarehouse.region',
            'destinationWarehouse.region',
            'sender',
            'receiver',
            'items'
        ]);

        if ($adminRegionId) {
            $query->where(function ($q) use ($adminRegionId) {
                $q->whereHas('originWarehouse', fn($w) => $w->where('warehouse_region', $adminRegionId))
                  ->orWhereHas('destinationWarehouse', fn($w) => $w->where('warehouse_region', $adminRegionId));
            });
        }

        if (!empty($search)) {
            $query->where('transfer_id', 'iLIKE', "%{$search}%");
        }

        if (!empty($originWarehouseId)) {
            $query->where('warehouse_origin_id', $originWarehouseId);
        }

        if (!empty($destinationWarehouseId)) {
            $query->where('warehouse_destination_id', $destinationWarehouseId);
        }

        if (!empty($selectedStatus)) {
            $query->where('transfer_status', $selectedStatus);
        }

        $transfers = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $warehouses = WarehouseMaster::with('region')->where('warehouse_status', 'active')->orderBy('warehouse_name', 'asc')->get();

        $originWarehousesQuery = WarehouseMaster::with('region')->where('warehouse_status', 'active');
        if ($adminRegionId) {
            $originWarehousesQuery->where('warehouse_region', $adminRegionId);
        }
        $userOriginWarehouses = $originWarehousesQuery->orderBy('warehouse_name', 'asc')->get();

        $items = ItemMaster::where('item_status', 'available')->orderBy('item_name', 'asc')->get();

        return view('transfer.index', compact(
            'transfers',
            'warehouses',
            'userOriginWarehouses',
            'items',
            'search',
            'originWarehouseId',
            'destinationWarehouseId',
            'selectedStatus'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_origin_id' => 'required|exists:warehouse_master,id',
            'warehouse_destination_id' => 'required|exists:warehouse_master,id|different:warehouse_origin_id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.qty_sent' => 'required|integer|min:1',
            'transfer_notes' => 'nullable|string',
        ], [
            'warehouse_destination_id.different' => 'Gudang tujuan harus berbeda dari gudang asal.',
            'warehouse_destination_id.required' => 'Gudang tujuan wajib dipilih.',
            'warehouse_origin_id.required' => 'Gudang asal wajib dipilih.',
        ]);

        if ($request->warehouse_origin_id == $request->warehouse_destination_id) {
            return redirect()->back()->with('error', 'Gudang asal dan gudang tujuan tidak boleh sama.');
        }

        return DB::transaction(function () use ($request) {
            $originWarehouse = WarehouseMaster::with('region')->findOrFail($request->warehouse_origin_id);
            $destinationWarehouse = WarehouseMaster::with('region')->findOrFail($request->warehouse_destination_id);

            $admin = Auth::user();
            if ($admin && $admin->region_id && $originWarehouse->warehouse_region != $admin->region_id) {
                return redirect()->back()->with('error', 'Unauthorized action. Gudang asal tidak sesuai dengan region akun Anda.');
            }

            foreach ($request->items as $itemData) {
                $stock = StockMaster::where('warehouse_id', $originWarehouse->id)
                    ->where('item_id', $itemData['item_id'])
                    ->first();

                $currentStock = $stock ? $stock->stock_now : 0;
                if ($currentStock < $itemData['qty_sent']) {
                    $itemMaster = ItemMaster::find($itemData['item_id']);
                    return redirect()->back()->with('error', "Stok barang '{$itemMaster->item_variant}' di gudang asal tidak mencukupi (Tersedia: {$currentStock}, Diminta: {$itemData['qty_sent']}).");
                }
            }

            $originAlias = $originWarehouse->region->region_alias ?? 'ORG';
            $destAlias = $destinationWarehouse->region->region_alias ?? 'DST';

            $latestTransfer = StockTransferMaster::where('warehouse_origin_id', $originWarehouse->id)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($latestTransfer && preg_match('/-(\d{3})$/', $latestTransfer->transfer_id, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            } else {
                $count = StockTransferMaster::where('warehouse_origin_id', $originWarehouse->id)->count();
                $nextNumber = $count + 1;
            }

            $transferId = sprintf('TRF-%s-%s-%03d', $originAlias, $destAlias, $nextNumber);

            while (StockTransferMaster::where('transfer_id', $transferId)->exists()) {
                $nextNumber++;
                $transferId = sprintf('TRF-%s-%s-%03d', $originAlias, $destAlias, $nextNumber);
            }

            $transfer = StockTransferMaster::create([
                'transfer_id' => $transferId,
                'warehouse_origin_id' => $originWarehouse->id,
                'warehouse_destination_id' => $destinationWarehouse->id,
                'transfer_status' => 'draft',
                'sender_admin_id' => Auth::id(),
                'transfer_notes' => $request->transfer_notes,
            ]);

            foreach ($request->items as $itemData) {
                $itemMaster = ItemMaster::findOrFail($itemData['item_id']);

                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'item_id' => $itemMaster->id,
                    'item_sku' => $itemMaster->item_sku,
                    'item_name' => $itemMaster->item_name,
                    'item_sku_variant' => $itemMaster->item_sku_variant,
                    'item_variant' => $itemMaster->item_variant,
                    'qty_sent' => $itemData['qty_sent'],
                    'qty_received' => 0,
                ]);
            }

            return redirect()->route('transfer.index')->with('success', "Dokumen Transfer {$transferId} berhasil dibuat (Draft).");
        });
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'transfer_status' => 'required|in:in_transit,completed,cancelled',
        ]);

        return DB::transaction(function () use ($id, $request) {
            $transfer = StockTransferMaster::with('items')->where('id', $id)->lockForUpdate()->firstOrFail();
            $targetStatus = $request->transfer_status;

            if ($transfer->transfer_status === 'completed' || $transfer->transfer_status === 'cancelled') {
                return redirect()->back()->with('error', 'Dokumen transfer sudah selesai atau dibatalkan.');
            }

            if ($targetStatus === 'in_transit') {
                if ($transfer->transfer_status !== 'draft') {
                    return redirect()->back()->with('error', 'Hanya dokumen berstatus Draft yang dapat dikirim.');
                }

                foreach ($transfer->items as $trfItem) {
                    $stock = StockMaster::where('warehouse_id', $transfer->warehouse_origin_id)
                        ->where('item_id', $trfItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    $historyBefore = $stock ? (int) $stock->stock_now : 0;
                    $historyChange = (int) $trfItem->qty_sent;

                    if ($historyBefore < $historyChange) {
                        return redirect()->back()->with('error', "Stok item '{$trfItem->item_variant}' tidak mencukupi di gudang asal.");
                    }

                    $historyNow = $historyBefore - $historyChange;

                    $stock->update([
                        'stock_now' => $historyNow,
                    ]);

                    StockHistory::create([
                        'warehouse_id' => $transfer->warehouse_origin_id,
                        'item_id' => $trfItem->item_id,
                        'history_before' => $historyBefore,
                        'history_change' => $historyChange,
                        'history_now' => $historyNow,
                        'history_change__type' => 'decreased',
                        'history_change_description' => "stock_transfer_out ({$transfer->transfer_id})",
                    ]);
                }

                $transfer->update([
                    'transfer_status' => 'in_transit',
                    'transfer_date_sent' => now(),
                ]);

                return redirect()->back()->with('success', "Transfer {$transfer->transfer_id} telah dikirim (In Transit) & stok gudang asal telah dipotong.");
            }

            if ($targetStatus === 'completed') {
                if ($transfer->transfer_status !== 'in_transit') {
                    return redirect()->back()->with('error', 'Hanya dokumen berstatus In Transit yang dapat diselesaikan.');
                }

                foreach ($transfer->items as $trfItem) {
                    $itemMaster = ItemMaster::findOrFail($trfItem->item_id);

                    $stock = StockMaster::where('warehouse_id', $transfer->warehouse_destination_id)
                        ->where('item_id', $trfItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    $historyBefore = $stock ? (int) $stock->stock_now : 0;
                    $historyChange = (int) $trfItem->qty_sent;
                    $historyNow = $historyBefore + $historyChange;

                    if ($stock) {
                        $stock->update([
                            'stock_now' => $historyNow,
                        ]);
                    } else {
                        StockMaster::create([
                            'warehouse_id' => $transfer->warehouse_destination_id,
                            'item_id' => $trfItem->item_id,
                            'item_name' => $itemMaster->item_name,
                            'stock_now' => $historyNow,
                        ]);
                    }

                    $trfItem->update([
                        'qty_received' => $historyChange,
                    ]);

                    StockHistory::create([
                        'warehouse_id' => $transfer->warehouse_destination_id,
                        'item_id' => $trfItem->item_id,
                        'history_before' => $historyBefore,
                        'history_change' => $historyChange,
                        'history_now' => $historyNow,
                        'history_change__type' => 'increased',
                        'history_change_description' => "stock_transfer_in ({$transfer->transfer_id})",
                    ]);
                }

                $transfer->update([
                    'transfer_status' => 'completed',
                    'receiver_admin_id' => Auth::id(),
                    'transfer_date_received' => now(),
                ]);

                return redirect()->back()->with('success', "Transfer {$transfer->transfer_id} selesai & stok telah ditambahkan ke gudang tujuan.");
            }

            if ($targetStatus === 'cancelled') {
                if ($transfer->transfer_status === 'in_transit') {
                    foreach ($transfer->items as $trfItem) {
                        $itemMaster = ItemMaster::findOrFail($trfItem->item_id);

                        $stock = StockMaster::where('warehouse_id', $transfer->warehouse_origin_id)
                            ->where('item_id', $trfItem->item_id)
                            ->lockForUpdate()
                            ->first();

                        $historyBefore = $stock ? (int) $stock->stock_now : 0;
                        $historyChange = (int) $trfItem->qty_sent;
                        $historyNow = $historyBefore + $historyChange;

                        if ($stock) {
                            $stock->update([
                                'stock_now' => $historyNow,
                            ]);
                        } else {
                            StockMaster::create([
                                'warehouse_id' => $transfer->warehouse_origin_id,
                                'item_id' => $trfItem->item_id,
                                'item_name' => $itemMaster->item_name,
                                'stock_now' => $historyNow,
                            ]);
                        }

                        StockHistory::create([
                            'warehouse_id' => $transfer->warehouse_origin_id,
                            'item_id' => $trfItem->item_id,
                            'history_before' => $historyBefore,
                            'history_change' => $historyChange,
                            'history_now' => $historyNow,
                            'history_change__type' => 'increased',
                            'history_change_description' => "stock_transfer_cancelled_rollback ({$transfer->transfer_id})",
                        ]);
                    }
                }

                $transfer->update([
                    'transfer_status' => 'cancelled',
                ]);

                return redirect()->back()->with('success', "Transfer {$transfer->transfer_id} berhasil dibatalkan.");
            }

            return redirect()->back()->with('info', 'Tidak ada perubahan status.');
        });
    }

    public function getWarehouseStocks(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        if (!$warehouseId) {
            return response()->json([]);
        }

        $stocks = StockMaster::where('warehouse_id', $warehouseId)
            ->pluck('stock_now', 'item_id');

        return response()->json($stocks);
    }

    public function print($id)
    {
        $transfer = StockTransferMaster::with([
            'originWarehouse.region',
            'destinationWarehouse.region',
            'sender',
            'receiver',
            'items.item'
        ])->findOrFail($id);

        return view('transfer.print', compact('transfer'));
    }
}