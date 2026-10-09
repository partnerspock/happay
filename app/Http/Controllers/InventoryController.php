<?php

namespace App\Http\Controllers;

use App\Models\InventoryMaster;
use App\Models\InventoryMasterItem;
use App\Models\ItemMaster;
use App\Models\StockMaster;
use App\Models\StockHistory;
use App\Models\WarehouseMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $selectedWarehouse = $request->input('warehouse_id', '');
        $selectedType = $request->input('inventory_type', '');
        $selectedStatus = $request->input('inventory_status', '');

        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

        if (empty($allowedWarehouseIds)) {
            $inventories = collect()->paginate(15);
            $warehouses = collect();
            $userWarehouses = collect();
            $items = ItemMaster::where('item_status', 'available')->orderBy('item_name', 'asc')->get();

            return view('inventory.index', compact(
                'inventories',
                'warehouses',
                'userWarehouses',
                'items',
                'search',
                'selectedWarehouse',
                'selectedType',
                'selectedStatus'
            ));
        }

        $query = InventoryMaster::with(['warehouse.region', 'creator', 'items'])
            ->whereIn('warehouse_id', $allowedWarehouseIds);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('inventory_id', 'iLIKE', "%{$search}%")
                  ->orWhere('inventory_sender_name', 'iLIKE', "%{$search}%")
                  ->orWhere('inventory_sender_recipient', 'iLIKE', "%{$search}%");
            });
        }

        if (!empty($selectedWarehouse)) {
            if (in_array($selectedWarehouse, $allowedWarehouseIds)) {
                $query->where('warehouse_id', $selectedWarehouse);
            }
        }

        if (!empty($selectedType)) {
            $query->where('inventory_type', $selectedType);
        }

        if (!empty($selectedStatus)) {
            $query->where('inventory_status', $selectedStatus);
        }

        $inventories = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Warehouse untuk filter dan modal (hanya yang diizinkan untuk admin tersebut)
        $warehouses = WarehouseMaster::with('region')
            ->whereIn('id', $allowedWarehouseIds)
            ->where('warehouse_status', 'active')
            ->orderBy('warehouse_name', 'asc')
            ->get();

        $userWarehouses = $warehouses;

        $items = ItemMaster::where('item_status', 'available')->orderBy('item_name', 'asc')->get();

        return view('inventory.index', compact(
            'inventories',
            'warehouses',
            'userWarehouses',
            'items',
            'search',
            'selectedWarehouse',
            'selectedType',
            'selectedStatus'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouse_master,id',
            'inventory_type' => 'required|in:inbound,outbound',
            'inventory_sender_name' => 'required|string|max:255',
            'inventory_sender_contact' => 'required|string|max:255',
            'inventory_sender_logistic_info' => 'required|string|max:255',
            'inventory_sender_recipient' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:item_master,id',
            'items.*.inventory_qty_sent' => 'required|integer|min:1',
            'inventory_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'inventory_notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $admin = Auth::user();
            $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

            // Validasi keamanan hak akses warehouse
            if (!in_array($request->warehouse_id, $allowedWarehouseIds)) {
                return redirect()->back()->with('error', 'Unauthorized action. Anda tidak memiliki akses ke warehouse ini.');
            }

            $warehouse = WarehouseMaster::with('region')->findOrFail($request->warehouse_id);

            $typeCode = $request->inventory_type === 'inbound' ? 'IN' : 'OUT';
            $regionAlias = $warehouse->region->region_alias ?? 'GEN';

            $latestInventory = InventoryMaster::where('warehouse_id', $warehouse->id)
                ->where('inventory_type', $request->inventory_type)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($latestInventory && preg_match('/-(\d{3})$/', $latestInventory->inventory_id, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            } else {
                $count = InventoryMaster::where('warehouse_id', $warehouse->id)
                    ->where('inventory_type', $request->inventory_type)
                    ->count();
                $nextNumber = $count + 1;
            }

            $inventoryId = sprintf('INM-%s-%s-%03d', $typeCode, $regionAlias, $nextNumber);

            while (InventoryMaster::where('inventory_id', $inventoryId)->exists()) {
                $nextNumber++;
                $inventoryId = sprintf('INM-%s-%s-%03d', $typeCode, $regionAlias, $nextNumber);
            }

            $imagePath = null;
            if ($request->hasFile('inventory_image')) {
                $imagePath = $request->file('inventory_image')->store('inventory_proofs', 'public');
            }

            $inventory = InventoryMaster::create([
                'inventory_id' => $inventoryId,
                'warehouse_id' => $request->warehouse_id,
                'inventory_type' => $request->inventory_type,
                'inventory_status' => 'created',
                'inventory_sender_name' => $request->inventory_sender_name,
                'inventory_sender_contact' => $request->inventory_sender_contact,
                'inventory_sender_logistic_info' => $request->inventory_sender_logistic_info,
                'inventory_sender_recipient' => $request->inventory_sender_recipient,
                'inventory_sender_image' => $imagePath,
                'inventory_notes' => $request->inventory_notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $itemData) {
                $itemMaster = ItemMaster::findOrFail($itemData['item_id']);

                InventoryMasterItem::create([
                    'inventory_id' => $inventory->id,
                    'item_id' => $itemMaster->id,
                    'item_sku' => $itemMaster->item_sku,
                    'item_name' => $itemMaster->item_name,
                    'item_sku_variant' => $itemMaster->item_sku_variant,
                    'item_variant' => $itemMaster->item_variant,
                    'inventory_qty_sent' => $itemData['inventory_qty_sent'],
                    'inventory_qty_change' => 0,
                ]);
            }

            return redirect()->route('inventory.index')->with('success', "Dokumen {$inventoryId} berhasil dibuat.");
        });
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'inventory_status' => 'required|in:created,arrived,completed',
        ]);

        return DB::transaction(function () use ($id, $request) {
            $admin = Auth::user();
            $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

            $inventory = InventoryMaster::with('items')->where('id', $id)->lockForUpdate()->firstOrFail();

            if (!in_array($inventory->warehouse_id, $allowedWarehouseIds)) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            $targetStatus = $request->inventory_status;

            if ($inventory->inventory_status === 'completed') {
                return redirect()->back()->with('error', 'Inventory sudah berstatus Completed.');
            }

            if ($targetStatus === 'arrived') {
                $inventory->update([
                    'inventory_status' => 'arrived',
                    'inventory_date_arrived' => now(),
                ]);

                return redirect()->back()->with('success', 'Status inventory berhasil diperbarui menjadi Arrived.');
            }

            if ($targetStatus === 'completed') {
                $inventory->update([
                    'inventory_status' => 'completed',
                    'inventory_date_completed' => now(),
                ]);

                foreach ($inventory->items as $invItem) {
                    $itemMaster = ItemMaster::findOrFail($invItem->item_id);

                    $stock = StockMaster::where('warehouse_id', $inventory->warehouse_id)
                        ->where('item_id', $invItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    $historyBefore = $stock ? (int) $stock->stock_now : 0;
                    $historyChange = (int) $invItem->inventory_qty_sent;

                    if ($inventory->inventory_type === 'inbound') {
                        $historyNow = $historyBefore + $historyChange;
                        $changeType = 'increased';
                    } else {
                        $historyNow = max(0, $historyBefore - $historyChange);
                        $changeType = 'decreased';
                    }

                    if ($stock) {
                        $stock->update([
                            'stock_now' => $historyNow,
                        ]);
                    } else {
                        StockMaster::create([
                            'warehouse_id' => $inventory->warehouse_id,
                            'item_id' => $invItem->item_id,
                            'item_name' => $itemMaster->item_name,
                            'stock_now' => $historyNow,
                        ]);
                    }

                    $invItem->update([
                        'inventory_qty_change' => $historyChange,
                    ]);

                    StockHistory::create([
                        'warehouse_id' => $inventory->warehouse_id,
                        'item_id' => $invItem->item_id,
                        'history_before' => $historyBefore,
                        'history_change' => $historyChange,
                        'history_now' => $historyNow,
                        'history_change__type' => $changeType,
                        'history_change_description' => "inventory_" . $inventory->inventory_type . " ({$inventory->inventory_id})",
                    ]);
                }

                return redirect()->back()->with('success', 'Status inventory Completed & stok telah di-push.');
            }

            return redirect()->back()->with('info', 'Tidak ada perubahan status.');
        });
    }

    public function print($id)
    {
        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

        $inventory = InventoryMaster::with(['warehouse.region', 'creator', 'items.item'])->findOrFail($id);

        if (!in_array($inventory->warehouse_id, $allowedWarehouseIds)) {
            abort(403, 'Unauthorized action.');
        }

        return view('inventory.print', compact('inventory'));
    }
}