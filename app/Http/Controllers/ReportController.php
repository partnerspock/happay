<?php

namespace App\Http\Controllers;

use App\Models\PosTransactionMaster;
use App\Models\WarehouseMaster;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    // 1. TRANSACTION REPORT
    public function transactionReport(Request $request)
    {
        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();
        $lockedWarehouseId = $admin->warehouse_id ?? null;

        $search = $request->input('search', '');
        $selectedWarehouse = $request->input('warehouse_id', '');
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        if (!empty($lockedWarehouseId)) {
            $selectedWarehouse = $lockedWarehouseId;
        }

        $query = PosTransactionMaster::with(['warehouse.region', 'cashier', 'items.item'])
            ->whereIn('warehouse_id', $allowedWarehouseIds);

        if (!empty($selectedWarehouse) && in_array($selectedWarehouse, $allowedWarehouseIds)) {
            $query->where('warehouse_id', $selectedWarehouse);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('uuid', 'iLIKE', "%{$search}%")
                  ->orWhere('transaction_info_name', 'iLIKE', "%{$search}%")
                  ->orWhere('transaction_info_number', 'iLIKE', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('id', 'desc')->get();

        $warehouses = WarehouseMaster::with('region')
            ->whereIn('id', $allowedWarehouseIds)
            ->where('warehouse_status', 'active')
            ->orderBy('warehouse_name', 'asc')
            ->get();

        return view('report.transactions', compact(
            'transactions',
            'warehouses',
            'search',
            'selectedWarehouse',
            'startDate',
            'endDate',
            'lockedWarehouseId'
        ));
    }

    public function transactionDetail($id)
    {
        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

        $transaction = PosTransactionMaster::with(['warehouse.region', 'cashier', 'items.item'])
            ->where('id', $id)
            ->whereIn('warehouse_id', $allowedWarehouseIds)
            ->firstOrFail();

        return response()->json($transaction);
    }

    public function exportTransactionCsv(Request $request)
    {
        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

        $transactions = PosTransactionMaster::with(['warehouse', 'cashier', 'items'])
            ->whereIn('warehouse_id', $allowedWarehouseIds)
            ->orderBy('id', 'desc')
            ->get();

        $filename = "Transaction_Report_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['UUID', 'Date', 'Warehouse', 'Cashier', 'Customer Name', 'Phone', 'Status', 'Total Items', 'Total Price', 'Tax', 'Margin']);

            foreach ($transactions as $trx) {
                fputcsv($file, [
                    $trx->uuid,
                    $trx->created_at,
                    optional($trx->warehouse)->warehouse_name,
                    optional($trx->cashier)->admin_name,
                    $trx->transaction_info_name,
                    $trx->transaction_info_number,
                    $trx->transaction_status,
                    $trx->items->sum('transaction_item_qty'),
                    $trx->transaction_pricetotal,
                    $trx->transaction_tax,
                    $trx->transaction_margin,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // 2. ITEM REPORT
    public function itemReport(Request $request)
    {
        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();
        $lockedWarehouseId = $admin->warehouse_id ?? null;

        $search = $request->input('search', '');
        $selectedWarehouse = $request->input('warehouse_id', '');
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        if (!empty($lockedWarehouseId)) {
            $selectedWarehouse = $lockedWarehouseId;
        }

        $query = StockHistory::with(['warehouse.region', 'item'])
            ->whereIn('warehouse_id', $allowedWarehouseIds);

        if (!empty($selectedWarehouse) && in_array($selectedWarehouse, $allowedWarehouseIds)) {
            $query->where('warehouse_id', $selectedWarehouse);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }

        if (!empty($search)) {
            $query->whereHas('item', function($q) use ($search) {
                $q->where('item_name', 'iLIKE', "%{$search}%")
                  ->orWhere('item_sku', 'iLIKE', "%{$search}%");
            });
        }

        $histories = $query->orderBy('id', 'desc')->get();

        $warehouses = WarehouseMaster::with('region')
            ->whereIn('id', $allowedWarehouseIds)
            ->where('warehouse_status', 'active')
            ->orderBy('warehouse_name', 'asc')
            ->get();

        return view('report.items', compact(
            'histories',
            'warehouses',
            'search',
            'selectedWarehouse',
            'startDate',
            'endDate',
            'lockedWarehouseId'
        ));
    }

    public function exportItemCsv(Request $request)
    {
        $admin = Auth::user();
        $allowedWarehouseIds = $admin->getAllowedWarehouseIds();

        $histories = StockHistory::with(['warehouse', 'item'])
            ->whereIn('warehouse_id', $allowedWarehouseIds)
            ->orderBy('id', 'desc')
            ->get();

        $filename = "Item_Movement_Report_" . date('Y-m-d_H-i-s') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($histories) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Time', 'Warehouse', 'Item SKU', 'Item Name', 'Reference', 'Type', 'Qty Before', 'Change', 'Qty Now']);

            foreach ($histories as $h) {
                fputcsv($file, [
                    $h->created_at,
                    optional($h->warehouse)->warehouse_name,
                    optional($h->item)->item_sku,
                    optional($h->item)->item_name,
                    $h->history_change_description,
                    $h->history_change__type,
                    $h->history_before,
                    $h->history_change,
                    $h->history_now,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // 3. INVENTORY REPORT (Placeholder / Next)
    public function inventoryReport()
    {
        return view('report.inventory');
    }
}