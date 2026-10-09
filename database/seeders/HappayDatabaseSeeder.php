<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HappayDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Region
        $jktId = DB::table('region_master')->insertGetId([
            'region_alias' => 'JKT',
            'region_name' => 'Jakarta',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bdgId = DB::table('region_master')->insertGetId([
            'region_alias' => 'BDG',
            'region_name' => 'Bandung',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Seed Warehouse
        $whJkt1 = DB::table('warehouse_master')->insertGetId([
            'warehouse_region' => $jktId,
            'warehouse_name' => 'Headquarters Jakarta - Cikarang',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whJkt2 = DB::table('warehouse_master')->insertGetId([
            'warehouse_region' => $jktId,
            'warehouse_name' => 'Branch Jakarta - Pondok Indah',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $whBdg1 = DB::table('warehouse_master')->insertGetId([
            'warehouse_region' => $bdgId,
            'warehouse_name' => 'Headquarters Bandung - Cimahi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Seed Admin Roles
        $managerRoleId = DB::table('admin_role_master')->insertGetId([
            'role_name' => 'manager',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $spvRoleId = DB::table('admin_role_master')->insertGetId([
            'role_name' => 'spv',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cashierRoleId = DB::table('admin_role_master')->insertGetId([
            'role_name' => 'cashier',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $warehouseRoleId = DB::table('admin_role_master')->insertGetId([
            'role_name' => 'warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Seed Admin (Sesuai aturan role & branch)
        
        // Manager (Region & Warehouse null / global access)
        DB::table('admin_master')->insert([
            'region_id' => null,
            'warehouse_id' => null,
            'admin_name' => 'mgr',
            'admin_contact' => '081111111111',
            'admin_password' => Hash::make('123'),
            'admin_role' => $managerRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Supervisor JKT (Region terisi, warehouse null / region-wide access)
        DB::table('admin_master')->insert([
            'region_id' => $jktId,
            'warehouse_id' => null,
            'admin_name' => 'spv',
            'admin_contact' => '081234567890',
            'admin_password' => Hash::make('123'),
            'admin_role' => $spvRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kasir JKT (Region & Warehouse spesifik terisi)
        DB::table('admin_master')->insert([
            'region_id' => $jktId,
            'warehouse_id' => $whJkt2, // Terikat di Branch Pondok Indah
            'admin_name' => 'kasir',
            'admin_contact' => '081234567891',
            'admin_password' => Hash::make('123'),
            'admin_role' => $cashierRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Warehouse Staff JKT (Region & Warehouse spesifik terisi)
        DB::table('admin_master')->insert([
            'region_id' => $jktId,
            'warehouse_id' => $whJkt1, // Terikat di HQ Cikarang
            'admin_name' => 'Warehouse Cikarang',
            'admin_contact' => '081234567892',
            'admin_password' => Hash::make('password123'),
            'admin_role' => $warehouseRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Supervisor BDG
        DB::table('admin_master')->insert([
            'region_id' => $bdgId,
            'warehouse_id' => null,
            'admin_name' => 'Supervisor BDG',
            'admin_contact' => '081234567893',
            'admin_password' => Hash::make('password123'),
            'admin_role' => $spvRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Seed Item Master
        DB::table('item_master')->insert([
            [
                'item_sku' => 'LPT-APL-MAM1',
                'item_sku_variant' => 'LPT-APL-MAM1-SLV',
                'item_name' => 'Macbook Air M1',
                'item_variant' => 'Macbook Air M1 - Silver',
                'item_price' => 9000000,
                'item_cogs' => 7600000,
                'item_status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_sku' => 'LPT-APL-MAM1',
                'item_sku_variant' => 'LPT-APL-MAM1-BLK',
                'item_name' => 'Macbook Air M1',
                'item_variant' => 'Macbook Air M1 - Midnight Black',
                'item_price' => 9000000,
                'item_cogs' => 7600000,
                'item_status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_sku' => 'LPT-APL-MPM4',
                'item_sku_variant' => 'LPT-APL-MPM4-SLV',
                'item_name' => 'Macbook Pro M4',
                'item_variant' => 'Macbook Pro M4 - Silver',
                'item_price' => 29000000,
                'item_cogs' => 21000000,
                'item_status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_sku' => 'OSX-260-TAHO',
                'item_sku_variant' => 'OSX-260-TAHO-261',
                'item_name' => 'MacOS Tahoe 26',
                'item_variant' => 'MacOS Tahoe 26 - Update 26.1',
                'item_price' => 899000,
                'item_cogs' => 99000,
                'item_status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}