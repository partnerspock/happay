<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseMaster extends Model
{
    protected $table = 'warehouse_master';

    protected $fillable = [
        'warehouse_region',
        'warehouse_name',
        'warehouse_status',
        'warehouse_deleted_at',
    ];

    public function region()
    {
        return $this->belongsTo(RegionMaster::class, 'warehouse_region');
    }

    public function stocks()
    {
        return $this->hasMany(StockMaster::class, 'stock_warehouse');
    }
}