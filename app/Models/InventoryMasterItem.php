<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMasterItem extends Model
{
    protected $table = 'inventory_master_item';

    protected $fillable = [
        'inventory_id',
        'item_id',
        'item_sku',
        'item_name',
        'item_sku_variant',
        'item_variant',
        'inventory_qty_sent',
        'inventory_qty_change',
    ];

    public function inventory()
    {
        return $this->belongsTo(InventoryMaster::class, 'inventory_id');
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}