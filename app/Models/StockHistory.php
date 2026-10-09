<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $table = 'stock_history';

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'history_before',
        'history_change',
        'history_now',
        'history_change__type',
        'history_change_description',
    ];

    public function warehouse()
    {
        return $this->belongsTo(WarehouseMaster::class, 'warehouse_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(AdminMaster::class, 'created_by');
    }
}