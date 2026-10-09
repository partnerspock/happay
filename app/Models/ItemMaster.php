<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemMaster extends Model
{
    protected $table = 'item_master';

    protected $fillable = [
        'item_sku',
        'item_sku_variant',
        'item_name',
        'item_variant',
        'item_image',
        'item_variant_image',
        'item_price',
        'item_cogs',
        'item_lifetime',
        'item_status',
    ];

    protected $casts = [
        'item_price' => 'decimal:2',
        'item_cogs' => 'decimal:2',
        'item_lifetime' => 'datetime',
    ];

    public function stocks()
    {
        return $this->hasMany(StockMaster::class, 'item_id');
    }
}