<?php

namespace App\Http\Controllers;

use App\Models\PosTransactionMaster;
use App\Models\PosTransactionItemMaster;
use App\Models\WarehouseMaster;
use App\Models\StockMaster;
use App\Models\StockHistory;
use App\Models\ItemMaster;
use App\Models\AdminMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $selectedWarehouse = $request->input('warehouse_id', '');
        $selectedStatus = $request->input('transaction_status', '');

        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

        if (empty($allowedWarehouseIds)) {
            $transactions = collect()->paginate(15);
            $warehouses = collect();
            return view('pos.index', compact('transactions', 'warehouses', 'search', 'selectedWarehouse', 'selectedStatus'));
        }

        $query = PosTransactionMaster::with(['warehouse.region', 'cashier', 'items.item'])
            ->whereIn('warehouse_id', $allowedWarehouseIds);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('uuid', 'iLIKE', "%{$search}%")
                  ->orWhere('transaction_info_name', 'iLIKE', "%{$search}%");
            });
        }

        if (!empty($selectedWarehouse)) {
            if (in_array($selectedWarehouse, $allowedWarehouseIds)) {
                $query->where('warehouse_id', $selectedWarehouse);
            }
        }

        if (!empty($selectedStatus)) {
            $query->where('transaction_status', $selectedStatus);
        }

        $transactions = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $warehouses = WarehouseMaster::with('region')
            ->whereIn('id', $allowedWarehouseIds)
            ->where('warehouse_status', 'active')
            ->orderBy('warehouse_name', 'asc')
            ->get();

        return view('pos.index', compact(
            'transactions',
            'warehouses',
            'search',
            'selectedWarehouse',
            'selectedStatus'
        ));
    }

    public function create(Request $request)
    {
        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

        $warehouses = WarehouseMaster::with('region')
            ->whereIn('id', $allowedWarehouseIds)
            ->where('warehouse_status', 'active')
            ->orderBy('warehouse_name', 'asc')
            ->get();

        $defaultWarehouseId = $admin->getDefaultWarehouseId();

        // TAMBAHKAN BLOK INI: Mengambil data transaksi draft untuk dilanjutkan
        $continueTransaction = null;
        if ($request->has('continue')) {
            $continueTransaction = PosTransactionMaster::with(['items.item', 'warehouse'])
                ->whereIn('warehouse_id', $allowedWarehouseIds)
                ->find($request->continue);

            // Jika transaksi draft ditemukan, otomatis set default warehouse-nya sesuai warehouse transaksi tersebut
            if ($continueTransaction) {
                $defaultWarehouseId = $continueTransaction->warehouse_id;
            }
        }

        return view('pos.create', compact('warehouses', 'defaultWarehouseId', 'continueTransaction'));
    }

    public function getStockItems(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        if (!$warehouseId) {
            return response()->json([]);
        }

        $stocks = StockMaster::with('item')
            ->where('warehouse_id', $warehouseId)
            ->where('stock_now', '>', 0)
            ->get()
            ->map(function ($stock) {
                return [
                    'stock_master_id' => $stock->id,
                    'item_id' => $stock->item_id,
                    'item_sku' => $stock->item->item_sku ?? '-',
                    'item_name' => $stock->item->item_name ?? '-',
                    'item_variant' => $stock->item->item_variant ?? null,
                    'item_price' => $stock->item->item_price ?? 0,
                    'item_image' => $stock->item->item_image ?? null,
                    'stock_now' => $stock->stock_now,
                ];
            });

        return response()->json($stocks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouse_master,id',
            'transaction_info_name' => 'required|string|max:255',
            'transaction_info_number' => 'required|string|max:255',
            'transaction_status' => 'required|in:completed,draft',
            'items' => 'required|array|min:1',
            'items.*.stock_master_id' => 'required|exists:stock_master,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $admin = Auth::user();
            $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

            if (!in_array($request->warehouse_id, $allowedWarehouseIds)) {
                return redirect()->back()->with('error', 'Unauthorized action. Anda tidak memiliki akses ke warehouse ini.');
            }

            $warehouse = WarehouseMaster::with('region')->findOrFail($request->warehouse_id);
            $regionAlias = optional($warehouse->region)->region_alias ?? 'GEN';
            $datePrefix = Carbon::now()->format('Ym');

            $latestTrx = PosTransactionMaster::where('warehouse_id', $warehouse->id)
                ->where('uuid', 'LIKE', "INV-{$regionAlias}-{$datePrefix}%")
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($latestTrx && preg_match('/-(\d{3})$/', $latestTrx->uuid, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            }

            // UUID dibentuk bersih tanpa backslash
            $uuid = sprintf('INV-%s-%s%03d', $regionAlias, $datePrefix, $nextNumber);

            while (PosTransactionMaster::where('uuid', $uuid)->exists()) {
                $nextNumber++;
                $uuid = sprintf('INV-%s-%s%03d', $regionAlias, $datePrefix, $nextNumber);
            }

            $totalPrice = 0;
            $totalMargin = 0;
            $itemsDataToInsert = [];

            foreach ($request->items as $itemData) {
                $stock = StockMaster::with('item')->where('id', $itemData['stock_master_id'])->lockForUpdate()->firstOrFail();
                
                if ($request->transaction_status === 'completed' && $stock->stock_now < $itemData['qty']) {
                    return redirect()->back()->with('error', "Stok untuk item '{$stock->item->item_name}' tidak mencukupi (Tersedia: {$stock->stock_now}).");
                }

                $qty = (int) $itemData['qty'];
                $pricePerItem = (float) $itemData['price'];
                $subtotal = $qty * $pricePerItem;
                $totalPrice += $subtotal;

                $cogs = (float) ($stock->item->item_cogs ?? 0);
                $marginPerItem = $pricePerItem - $cogs;
                $totalMargin += ($marginPerItem * $qty);

                $itemsDataToInsert[] = [
                    'stock_model' => $stock,
                    'stock_master_id' => $stock->id,
                    'item_id' => $stock->item_id,
                    'qty' => $qty,
                    'variant' => $stock->item->item_variant ?? null,
                    'price' => $pricePerItem,
                    'subtotal' => $subtotal,
                ];
            }

            $tax = $request->transaction_status === 'completed' ? ($totalPrice * 0.11) : 0;
            $finalMargin = $request->transaction_status === 'completed' ? $totalMargin : 0;

            $transaction = PosTransactionMaster::create([
                'uuid' => $uuid,
                'transaction_date' => $request->transaction_status === 'completed' ? now() : null,
                'transaction_status' => $request->transaction_status,
                'warehouse_id' => $warehouse->id,
                'cashier_id' => Auth::id(),
                'transaction_info_name' => $request->transaction_info_name,
                'transaction_info_number' => $request->transaction_info_number,
                'transaction_pricetotal' => $totalPrice,
                'transaction_tax' => $tax,
                'transaction_margin' => $finalMargin,
            ]);

            foreach ($itemsDataToInsert as $data) {
                PosTransactionItemMaster::create([
                    'transaction_id' => $transaction->id,
                    'stock_master_id' => $data['stock_master_id'],
                    'item_id' => $data['item_id'],
                    'transaction_item_qty' => $data['qty'],
                    'transaction_item_variant' => $data['variant'],
                    'transaction_item_priceperitem' => $data['price'],
                    'transaction_item_pricesubtotal' => $data['subtotal'],
                ]);

                if ($request->transaction_status === 'completed') {
                    $stock = $data['stock_model'];
                    $historyBefore = (int)$stock->stock_now;
                    $historyChange = (int) $data['qty'];
                    $historyNow = max(0, $historyBefore - $historyChange);

                    $stock->update(['stock_now' => $historyNow]);

                    StockHistory::create([
                        'warehouse_id' => $warehouse->id,
                        'item_id' => $data['item_id'],
                        'history_before' => $historyBefore,
                        'history_change' => $historyChange,
                        'history_now' => $historyNow,
                        'history_change__type' => 'decreased',
                        'history_change_description' => "pos_sale ({$transaction->uuid})",
                    ]);
                }
            }

            return redirect()->route('pos.index')->with('success', "Transaksi POS {$uuid} berhasil dibuat.");
        });
    }

    public function updateStatus(Request $request, $id)
    {
        $transaction = PosTransactionMaster::with('items.item')->findOrFail($id);
        $newStatus = $request->input('transaction_status');

        if ($newStatus === 'void') {
            $request->validate([
                'spv_username' => 'required|string',
                'spv_password' => 'required|string',
            ], [
                'spv_username.required' => 'Nama SPV/Manager wajib diisi untuk void.',
                'spv_password.required' => 'Password SPV/Manager wajib diisi untuk void.',
            ]);

            $spv = AdminMaster::where('admin_name', $request->spv_username)
                ->whereIn('admin_role', [1, 2])
                ->first();

            if (!$spv || !Hash::check($request->spv_password, $spv->admin_password)) {
                // Simpan session ID agar jika password salah, modal tetap terbuka untuk transaksi ini
                session()->flash('failed_trx_id', $transaction->id);
                return redirect()->back()->withErrors(['spv_password' => 'Verifikasi Gagal: Nama atau Password Supervisor/Manager tidak valid!']);
            }
        }

        // Jika status diubah menjadi void dari status sebelumnya (misal completed)
        if ($newStatus === 'void' && $transaction->transaction_status !== 'void') {
            foreach ($transaction->items as $item) {
                $stock = StockMaster::where('warehouse_id', $transaction->warehouse_id)
                    ->where('item_id', $item->item_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $historyBefore = (int) $stock->stock_now;
                    $historyChange = (int) $item->transaction_item_qty;
                    $historyNow = $historyBefore + $historyChange;

                    // 1. Tambah kembali stok ke master
                    $stock->update(['stock_now' => $historyNow]);

                    // 2. Catat ke StockHistory agar terekam di Item Report (Stock In)
                    StockHistory::create([
                        'warehouse_id' => $transaction->warehouse_id,
                        'item_id' => $item->item_id,
                        'history_before' => $historyBefore,
                        'history_change' => $historyChange,
                        'history_now' => $historyNow,
                        'history_change__type' => 'increased', // atau 'in'
                        'history_change_description' => "void_transaction ({$transaction->uuid})",
                    ]);
                }
            }
        }

        $transaction->update(['transaction_status' => $newStatus]);

        return back()->with('success', 'Status transaksi berhasil divoid dan stok telah dikembalikan ke gudang.');
    }

    public function print($id)
    {
        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

        $transaction = PosTransactionMaster::with(['warehouse.region', 'cashier', 'items.item'])->findOrFail($id);

        if (!in_array($transaction->warehouse_id, $allowedWarehouseIds)) {
            abort(403, 'Unauthorized action.');
        }

        return view('pos.print', compact('transaction'));
    }
}