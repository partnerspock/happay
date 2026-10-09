<?php

namespace App\Http\Controllers;

use App\Models\RegionMaster;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        if (auth()->user()->admin_role != 1) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke modul Region.');
        }

        $regions = RegionMaster::withCount('warehouses')->orderBy('id', 'asc')->get();
        return view('region.index', compact('regions'));
    }

    public function store(Request $request)
    {
    if (auth()->user()->admin_role != 1) {
        abort(403, 'Akses ditolak.');
    }

    $validated = $request->validate([
        'region_alias'  => 'required|string|size:3|unique:region_master,region_alias',
        'region_name'   => 'required|string|max:255',
        'region_status' => 'required|in:active,inactive',
    ], [
        'region_alias.required' => 'Alias region wajib diisi.',
        'region_alias.size'     => 'Alias region harus tepat 3 karakter (contoh: JKT).',
        'region_alias.unique'   => 'Alias region tersebut sudah digunakan.',
        'region_name.required'  => 'Nama region wajib diisi.',
    ]);

    $validated['region_alias'] = strtoupper($validated['region_alias']);

    if ($validated['region_status'] === 'inactive') {
        $validated['region_deleted_at'] = now();
    }

    RegionMaster::create($validated);

    return redirect()->route('region.index')->with('success', 'Region baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
{
    if (auth()->user()->admin_role != 1) abort(403);

    $region = RegionMaster::findOrFail($id);

    $validated = $request->validate([
        'region_name'   => 'required|string|max:255',
        'region_status' => 'required|in:active,inactive',
    ], [
        'region_name.required' => 'Nama region wajib diisi.',
    ]);

    // PROTEKSI: Jika user memilih status 'inactive', cek dulu warehouse aktifnya
    if ($validated['region_status'] === 'inactive') {
        $activeWarehousesCount = $region->warehouses()
            ->where('warehouse_status', 'active')
            ->count();

        if ($activeWarehousesCount > 0) {
            return redirect()->route('region.index')
                ->with('error', "Gagal menonaktifkan: Region '{$region->region_name}' masih memiliki {$activeWarehousesCount} Warehouse berstatus Active.");
        }

        if (!$region->region_deleted_at) {
            $validated['region_deleted_at'] = now();
        }
    } elseif ($validated['region_status'] === 'active') {
        $validated['region_deleted_at'] = null;
    }

    $region->update($validated);

    return redirect()->route('region.index')->with('success', 'Region berhasil diperbarui!');
}

    public function destroy($id)
    {
    if (auth()->user()->admin_role != 1) abort(403);

    $region = RegionMaster::findOrFail($id);

    // Proteksi yang sama untuk aksi Delete/Soft-Delete langsung
    $activeWarehousesCount = $region->warehouses()
        ->where('warehouse_status', 'active')
        ->count();

    if ($activeWarehousesCount > 0) {
        return redirect()->route('region.index')->withErrors([
            'region_alias' => "Gagal menonaktifkan: Region masih memiliki {$activeWarehousesCount} Warehouse aktif."
        ]);
    }

    $region->update([
        'region_status' => 'inactive',
        'region_deleted_at' => now(),
    ]);

    return redirect()->route('region.index')->with('success', 'Region berhasil dinonaktifkan!');
    }
}