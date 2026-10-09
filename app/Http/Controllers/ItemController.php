<?php

namespace App\Http\Controllers;

use App\Models\ItemMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->get('status');

        $items = ItemMaster::when($statusFilter, function ($query) use ($statusFilter) {
                return $query->where('item_status', $statusFilter);
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('items.index', compact('items', 'statusFilter'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_sku'           => 'required|string|max:100',
            'item_sku_variant'   => 'nullable|string|max:100|unique:item_master,item_sku_variant',
            'item_name'          => 'required|string|max:255',
            'item_variant'       => 'nullable|string|max:255',
            'item_image'         => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Model image wajib
            'item_variant_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Variant image opsional
            'item_price'         => 'required|numeric|min:0',
            'item_cogs'          => 'required|numeric|min:0',
            'item_lifetime'      => 'nullable|date',
            'item_status'        => 'required|in:available,hold,discontinue',
        ], [
            'item_sku.required'           => 'SKU barang wajib diisi.',
            'item_sku_variant.unique'     => 'SKU Variant tersebut sudah terdaftar.',
            'item_name.required'          => 'Nama barang wajib diisi.',
            'item_image.required'         => 'Gambar model utama wajib diunggah.',
            'item_image.image'            => 'File harus berupa gambar (jpeg, png, jpg, webp).',
            'item_price.required'         => 'Harga jual wajib diisi.',
            'item_cogs.required'          => 'HPP (COGS) wajib diisi.',
        ]);

        // Tangkap & Simpan File Model Image
        if ($request->hasFile('item_image')) {
            $validated['item_image'] = $request->file('item_image')->store('items/models', 'public');
        }

        // Tangkap & Simpan File Variant Image (Jika ada)
        if ($request->hasFile('item_variant_image')) {
            $validated['item_variant_image'] = $request->file('item_variant_image')->store('items/variants', 'public');
        }

        ItemMaster::create($validated);

        return redirect()->route('items.index')->with('success', 'Master Item / Varian berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $item = ItemMaster::findOrFail($id);

        $validated = $request->validate([
            'item_sku_variant'   => 'nullable|string|max:100|unique:item_master,item_sku_variant,' . $id,
            'item_name'          => 'required|string|max:255',
            'item_variant'       => 'nullable|string|max:255',
            'item_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Saat update bisa opsional jika tidak diganti
            'item_variant_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'item_price'         => 'required|numeric|min:0',
            'item_cogs'          => 'required|numeric|min:0',
            'item_lifetime'      => 'nullable|date',
            'item_status'        => 'required|in:available,hold,discontinue',
        ], [
            'item_sku_variant.unique'   => 'SKU Variant tersebut sudah terdaftar.',
            'item_name.required'        => 'Nama barang wajib diisi.',
            'item_price.required'       => 'Harga jual wajib diisi.',
            'item_cogs.required'        => 'HPP (COGS) wajib diisi.',
        ]);

        // Jika upload gambar model baru, hapus gambar lama & simpan yang baru
        if ($request->hasFile('item_image')) {
            if ($item->item_image && Storage::disk('public')->exists($item->item_image)) {
                Storage::disk('public')->delete($item->item_image);
            }
            $validated['item_image'] = $request->file('item_image')->store('items/models', 'public');
        }

        // Jika upload gambar variant baru, hapus gambar lama & simpan yang baru
        if ($request->hasFile('item_variant_image')) {
            if ($item->item_variant_image && Storage::disk('public')->exists($item->item_variant_image)) {
                Storage::disk('public')->delete($item->item_variant_image);
            }
            $validated['item_variant_image'] = $request->file('item_variant_image')->store('items/variants', 'public');
        }

        $item->update($validated);

        return redirect()->route('items.index')->with('success', 'Master Item berhasil diperbarui!');
    }
}