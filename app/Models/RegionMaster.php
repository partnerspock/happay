<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionMaster extends Model
{
    protected $table = 'region_master';

    protected $fillable = [
        'region_alias',
        'region_name',
        'region_status',
        'region_deleted_at',
    ];

    public function warehouses()
    {
        return $this->hasMany(WarehouseMaster::class, 'warehouse_region');
    }
}