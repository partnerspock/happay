<?php

namespace App\Services;

use App\Models\ItemMaster;
use App\Models\PosTransactionMaster;
use App\Models\PosTransactionItemMaster;
use App\Models\StockMaster;
use Illuminate\Support\Facades\DB;
use Exception;

class PosService
{
    /**
     * Generate Custom UUID Format: YYYYMM-REGION-0001
     */
    public function generateUuid(string $regionAlias): string
    {
        $prefix = date('Ym') . '-' . strtoupper($regionAlias) . '-';

        $lastTransaction = PosTransactionMaster::where('uuid', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastTransaction) {
            $number = 1;
        } else {
            $lastNumber = (int) substr($lastTransaction->uuid, -4);
            $number = $lastNumber + 1;
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Store POS Transaction & Deduct Stock
     */
    public function checkout(array $data): PosTransactionMaster
    {
        return DB::transaction(function () use ($data) {
            $regionAlias = $data['region_alias'];
            $warehouseId = $data['warehouse_id'];
            $itemsPayload = $data['items']; // Array of ['item_id' => x, 'qty' => y]
            $taxRate = $data['tax_rate'] ?? 0; // misal 0.11 untuk PPN 11%

            $totalPrice = 0;
            $totalCogs = 0;
            $preparedItems = [];

            // 1. Process items and calculate totals
            foreach ($itemsPayload as $payloadItem) {
                $item = ItemMaster::findOrFail($payloadItem['item_id']);
                $qty = $payloadItem['qty'];

                // Check Stock Availability
                $latestStock = StockMaster::where('stock_warehouse', $warehouseId)
                    ->where('item_id', $item->id)
                    ->orderBy('id', 'desc')
                    ->first();

                $currentStock = $latestStock ? $latestStock->stock_now : 0;
                if ($currentStock < $qty) {
                    throw new Exception("Stok barang '{$item->item_name}' tidak mencukupi. Sisa stok: {$currentStock}");
                }

                $itemPriceTotal = $item->item_price * $qty;
                $itemCogsTotal = $item->item_cogs * $qty;

                $totalPrice += $itemPriceTotal;
                $totalCogs += $itemCogsTotal;

                $preparedItems[] = [
                    'item' => $item,
                    'qty' => $qty,
                    'price_per_item' => $item->item_price,
                    'price_total' => $itemPriceTotal,
                    'current_stock' => $currentStock
                ];
            }

            // 2. Calculate Tax & Net Margin
            $taxAmount = $totalPrice * $taxRate;
            $netMargin = ($totalPrice - $taxAmount) - $totalCogs;

            // 3. Save Transaction Master
            $transaction = PosTransactionMaster::create([
                'uuid' => $this->generateUuid($regionAlias),
                'transaction_date' => now(),
                'transaction_status' => 'completed',
                'transaction_info_name' => $data['customer_name'] ?? 'Guest',
                'transaction_info_number' => $data['customer_phone'] ?? null,
                'transaction_pricetotal' => $totalPrice,
                'transaction_cogs' => $totalCogs,
                'transaction_tax' => $taxAmount,
                'transaction_margin' => $netMargin,
            ]);

            // 4. Save Items & Deduct Stock
            foreach ($preparedItems as $prep) {
                $item = $prep['item'];
                $qty = $prep['qty'];

                PosTransactionItemMaster::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $item->id,
                    'transaction_item_qty' => $qty,
                    'transaction_item_variant' => $item->item_variant,
                    'transaction_item_priceperitem' => $prep['price_per_item'],
                    'transaction_item_pricetotal' => $prep['price_total'],
                ]);

                // Record Stock Mutation
                StockMaster::create([
                    'stock_warehouse' => $warehouseId,
                    'item_id' => $item->id,
                    'stock_before' => $prep['current_stock'],
                    'stock_change' => $qty,
                    'stock_now' => $prep['current_stock'] - $qty,
                    'stock_change_type' => 'decreased',
                    'stock_change_description' => "POS Checkout TRX #{$transaction->uuid}",
                ]);
            }

            return $transaction->load('items.item');
        });
    }
}