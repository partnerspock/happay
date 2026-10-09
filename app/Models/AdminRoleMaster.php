<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRoleMaster extends Model
{
    protected $table = 'admin_role_master';
    protected $fillable = ['role_name'];

    public function admins()
    {
        return $this->hasMany(AdminMaster::class, 'admin_role');
    }
}