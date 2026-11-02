<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;

// 1. Redirect Halaman Utama (/) ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Main Features Routes (Wajib Login)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS Routes
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/list', [PosController::class, 'index'])->name('pos.list');
    Route::get('/pos/create', [PosController::class, 'create'])->name('pos.create');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    Route::patch('/pos/{id}/status', [PosController::class, 'updateStatus'])->name('pos.update-status');
    Route::get('/pos/{id}/print', [PosController::class, 'print'])->name('pos.print');
    Route::get('/pos/stocks', [PosController::class, 'getStockItems'])->name('pos.stocks');
    Route::get('/api/pos-stocks', [PosController::class, 'getStockItems'])->name('api.pos-stocks');

    // Master Items
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::put('/items/{id}', [ItemController::class, 'update'])->name('items.update');

    // Master Warehouse & Stock
    Route::get('/warehouse', [WarehouseController::class, 'index'])->name('warehouse.index');
    Route::get('/warehouse/stock', [WarehouseController::class, 'stock'])->name('warehouse.stock');
    Route::get('/warehouse/stock-history', [WarehouseController::class, 'stockHistory'])->name('warehouse.stock-history');
    Route::get('/warehouse/stock/{warehouse_id}/item/{item_id}/history', [WarehouseController::class, 'itemStockHistory'])->name('warehouse.stock.item-history');
    Route::post('/warehouse', [WarehouseController::class, 'store'])->name('warehouse.store');
    Route::put('/warehouse/{id}', [WarehouseController::class, 'update'])->name('warehouse.update');
    Route::get('/api/warehouse-stocks', [StockTransferController::class, 'getWarehouseStocks'])->name('api.warehouse-stocks');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::patch('/inventory/{id}/status', [InventoryController::class, 'updateStatus'])->name('inventory.update-status');
    Route::get('/inventory/{id}/print', [InventoryController::class, 'print'])->name('inventory.print');

    // Stock Transfer
    Route::get('/transfer', [StockTransferController::class, 'index'])->name('transfer.index');
    Route::post('/transfer', [StockTransferController::class, 'store'])->name('transfer.store');
    Route::patch('/transfer/{id}/status', [StockTransferController::class, 'updateStatus'])->name('transfer.update-status');
    Route::get('/transfer/{id}/print', [StockTransferController::class, 'print'])->name('transfer.print');

    // Master Region (Nanti difilter role SPV di controller/view)
    Route::get('/region', [RegionController::class, 'index'])->name('region.index');
    Route::post('/region', [RegionController::class, 'store'])->name('region.store');
    Route::put('/region/{id}', [RegionController::class, 'update'])->name('region.update');
    Route::delete('/region/{id}', [RegionController::class, 'destroy'])->name('region.destroy');

    Route::prefix('report')->name('report.')->middleware('auth')->group(function () {
        Route::get('/transactions', [ReportController::class, 'transactionReport'])->name('transactions');
        Route::get('/transactions/{id}/detail', [ReportController::class, 'transactionDetail'])->name('transactions.detail');
        Route::get('/transactions/export-csv', [ReportController::class, 'exportTransactionCsv'])->name('transactions.export.csv');
        Route::get('/items', [ReportController::class, 'itemReport'])->name('items');
        Route::get('/inventory', [ReportController::class, 'inventoryReport'])->name('inventory');

        Route::get('/items', [ReportController::class, 'itemReport'])->name('items');
        Route::get('/items/export-csv', [ReportController::class, 'exportItemCsv'])->name('items.export.csv');
    });
});