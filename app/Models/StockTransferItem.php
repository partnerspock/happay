<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $table = 'stock_transfer_item';

    protected $fillable = [
        'transfer_id',
        'item_id',
        'item_sku',
        'item_name',
        'item_sku_variant',
        'item_variant',
        'qty_sent',
        'qty_received',
    ];

    public function transfer()
    {
        return $this->belongsTo(StockTransferMaster::class, 'transfer_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}