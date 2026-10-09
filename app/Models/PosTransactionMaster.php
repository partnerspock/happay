<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTransactionMaster extends Model
{
    protected $table = 'pos_transaction_master';

    protected $fillable = [
        'uuid',
        'transaction_date',
        'transaction_status',
        'warehouse_id',
        'cashier_id',
        'transaction_info_name',
        'transaction_info_number',
        'transaction_pricetotal',
        'transaction_tax',
        'transaction_margin',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'transaction_pricetotal' => 'decimal:2',
        'transaction_tax' => 'decimal:2',
        'transaction_margin' => 'decimal:2',
    ];

    public function warehouse()
    {
        return $this->belongsTo(WarehouseMaster::class, 'warehouse_id');
    }

    public function cashier()
    {
        return $this->belongsTo(AdminMaster::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(PosTransactionItemMaster::class, 'transaction_id');
    }
}