<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMaster extends Model
{
    protected $table = 'stock_master';

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'item_name',
        'stock_now',
    ];

    public function warehouse()
    {
        return $this->belongsTo(WarehouseMaster::class, 'warehouse_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}