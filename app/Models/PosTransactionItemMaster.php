<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTransactionItemMaster extends Model
{
    protected $table = 'pos_transaction_item_master';

    protected $fillable = [
        'transaction_id',
        'stock_master_id',
        'item_id',
        'transaction_item_qty',
        'transaction_item_variant',
        'transaction_item_priceperitem',
        'transaction_item_pricesubtotal',
    ];

    protected $casts = [
        'transaction_item_priceperitem' => 'decimal:2',
        'transaction_item_pricesubtotal' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(PosTransactionMaster::class, 'transaction_id');
    }

    public function stockMaster()
    {
        return $this->belongsTo(StockMaster::class, 'stock_master_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}