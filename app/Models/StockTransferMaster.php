<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferMaster extends Model
{
    protected $table = 'stock_transfer_master';

    protected $fillable = [
        'transfer_id',
        'warehouse_origin_id',
        'warehouse_destination_id',
        'transfer_status',
        'sender_admin_id',
        'receiver_admin_id',
        'transfer_notes',
        'transfer_date_sent',
        'transfer_date_received',
    ];

    public function originWarehouse()
    {
        return $this->belongsTo(WarehouseMaster::class, 'warehouse_origin_id');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(WarehouseMaster::class, 'warehouse_destination_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class, 'transfer_id');
    }

    public function sender()
    {
        return $this->belongsTo(AdminMaster::class, 'sender_admin_id');
    }

    public function receiver()
    {
        return $this->belongsTo(AdminMaster::class, 'receiver_admin_id');
    }
}