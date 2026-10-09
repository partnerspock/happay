<?php

namespace App\Http\Controllers;

use App\Models\WarehouseMaster;
use App\Models\StockMaster;
use App\Models\StockHistory;
use App\Models\RegionMaster;
use App\Models\ItemMaster;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $selectedRegion = $request->get('region_id');

        $warehouses = WarehouseMaster::with('region')
            ->when($selectedRegion, function ($query) use ($selectedRegion) {
                return $query->where('warehouse_region', $selectedRegion);
            })
            ->orderBy('id', 'asc')
            ->get();

        $regions = RegionMaster::where('region_status', 'active')->get();

        return view('warehouse.index', compact('warehouses', 'regions', 'selectedRegion'));
    }

    public function stock(Request $request)
    {
        $warehouseId = $request->input('warehouse_id', '');
        $search      = $request->input('search', '');

        $query = StockMaster::with(['warehouse.region', 'item']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'ILIKE', "%{$search}%")
                  ->orWhereHas('item', function ($itemQuery) use ($search) {
                      $itemQuery->where('item_sku', 'ILIKE', "%{$search}%")
                                ->orWhere('item_name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($warehouseId)) {
            $query->where('warehouse_id', $warehouseId);
        }

        $stocks = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $warehouses = WarehouseMaster::with('region')->where('warehouse_status', 'active')->get();

        return view('warehouse.stock', compact('stocks', 'warehouses', 'warehouseId', 'search'));
    }

    public function stockIndex(Request $request)
    {
        return $this->stock($request);
    }

    public function stockHistory(Request $request)
    {
        $search = $request->input('search', '');
        $selectedWarehouse = $request->input('warehouse_id', '');

        $query = StockHistory::with(['warehouse.region', 'item']);

        if (!empty($search)) {
            $query->whereHas('item', function ($itemQuery) use ($search) {
                $itemQuery->where('item_name', 'ILIKE', "%{$search}%")
                    ->orWhere('item_sku', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($selectedWarehouse)) {
            $query->where('warehouse_id', $selectedWarehouse);
        }

        $histories = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $warehouses = WarehouseMaster::with('region')->where('warehouse_status', 'active')->get();

        return view('warehouse.stock-history', compact(
            'histories', 
            'warehouses', 
            'search', 
            'selectedWarehouse'
        ));
    }

    public function itemStockHistory(Request $request, $warehouseId, $itemId)
    {
        $warehouse = WarehouseMaster::with('region')->findOrFail($warehouseId);
        $item = ItemMaster::findOrFail($itemId);

        $histories = StockHistory::with(['warehouse.region', 'item'])
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('warehouse.item-stock-history', compact('warehouse', 'item', 'histories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_region' => 'required|exists:region_master,id',
            'warehouse_name'   => 'required|string|max:255',
            'warehouse_status' => 'required|in:active,inactive',
        ], [
            'warehouse_region.required' => 'Region wajib dipilih.',
            'warehouse_region.exists'   => 'Region tidak valid.',
            'warehouse_name.required'   => 'Nama warehouse wajib diisi.',
        ]);

        if ($validated['warehouse_status'] === 'inactive') {
            $validated['warehouse_deleted_at'] = now();
        }

        WarehouseMaster::create($validated);

        return redirect()->route('warehouse.index')->with('success', 'Warehouse berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $warehouse = WarehouseMaster::findOrFail($id);

        $validated = $request->validate([
            'warehouse_region' => 'required|exists:region_master,id',
            'warehouse_name'   => 'required|string|max:255',
            'warehouse_status' => 'required|in:active,inactive',
        ], [
            'warehouse_region.required' => 'Region wajib dipilih.',
            'warehouse_name.required'   => 'Nama warehouse wajib diisi.',
        ]);

        if ($validated['warehouse_status'] === 'inactive' && !$warehouse->warehouse_deleted_at) {
            $validated['warehouse_deleted_at'] = now();
        } elseif ($validated['warehouse_status'] === 'active') {
            $validated['warehouse_deleted_at'] = null;
        }

        $warehouse->update($validated);

        return redirect()->route('warehouse.index')->with('success', 'Warehouse berhasil diperbarui!');
    }
}