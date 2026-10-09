<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminMaster extends Authenticatable
{
    protected $table = 'admin_master';

    // Beritahu Laravel bahwa kolom password kita bernama 'admin_password'
    public function getAuthPassword()
    {
        return $this->admin_password;
    }
    
    public function role()
    {
        return $this->belongsTo(AdminRoleMaster::class, 'admin_role');
    }

    public function region()
    {
        return $this->belongsTo(RegionMaster::class, 'region_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(WarehouseMaster::class, 'warehouse_id');
    }

    public function getAllowedWarehouseIds()
    {
        $roleName = optional($this->role)->role_name;

        if ($roleName === 'manager') {
            return WarehouseMaster::where('warehouse_status', 'active')->pluck('id')->toArray();
        }

        if ($roleName === 'spv' && $this->region_id) {
            return WarehouseMaster::where('warehouse_status', 'active')
                ->where('warehouse_region', $this->region_id)
                ->pluck('id')->toArray();
        }

        if (in_array($roleName, ['cashier', 'warehouse']) && $this->warehouse_id) {
            return [$this->warehouse_id];
        }

        return [];
    }

    public function getDefaultWarehouseId()
    {
        $allowed = $this->getAllowedWarehouseIds();
        return count($allowed) > 0 ? $allowed[0] : null;
    }
}