<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMaster extends Model
{
    protected $table = 'inventory_master';

    protected $fillable = [
        'inventory_id',
        'warehouse_id',
        'inventory_type',
        'inventory_status',
        'inventory_sender_name',
        'inventory_sender_contact',
        'inventory_sender_logistic_info',
        'inventory_sender_recipient',
        'inventory_sender_image',
        'inventory_sender_video',
        'inventory_notes',
        'inventory_date_arrived',
        'inventory_date_completed',
        'created_by',
    ];

    protected $casts = [
        'inventory_date_arrived'   => 'datetime',
        'inventory_date_completed' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(WarehouseMaster::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryMasterItem::class, 'inventory_id');
    }

    // Perbaikan: Ubah User::class menjadi AdminMaster::class
    public function creator()
    {
        return $this->belongsTo(AdminMaster::class, 'created_by');
    }
}